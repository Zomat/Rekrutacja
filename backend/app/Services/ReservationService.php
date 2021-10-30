<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use App\Mail\CancelReservationMail;
use App\Mail\DeleteReservationMail;
use App\Models\CancellationRequest;
use App\Mail\ReservationSuccessMail;
use Illuminate\Support\Facades\Mail;
use App\Actions\CheckIfTableIsAvailableAction;
use App\Actions\GenerateCancellationRequestCodeAction;

class ReservationService{
    public $reservationId;
    private $errors = [];

    private $reservationModel;

    public function __construct(Reservation $reservationModel)
    {
        $this->reservationModel = $reservationModel;
    }

    /**
     * Get all reservations in given day.
     *
     * @param string $date
     * @return Collection
     */
    public function getAllReservationsByDay(string $date): Collection
    {
        $result = $this->reservationModel
            ->whereDate('dateStart', $date)
            ->get();

        $reservations = $result->map(function ($reservation) {

            $reservation->setAttribute('date', $reservation->dateStart);
            $reservation->setAttribute('duration', $reservation->getDuration());

            return $reservation->only([
                'date',
                'duration',
                'seatNumber',
                'fullName',
                'phone',
                'email',
                'numberOfSeats',
            ]);
        });

        return $reservations;
    }

    /**
     * Validate data, save reservation and send success mail.
     *
     * @param array $data
     * @return bool
     */
    public function makeReservation(array $data): bool
    {
        /**
         * Check if date is correct
         * If not set error and return false
         */
         if (!$this->checkIfDateIsCorrect($data['date'])) {
            $this->errors[] = 'Choose date in the future.';
            return false;
        }

        /**
         * Check if number of seats is valid
         * If not set error with message and return false
         */
        $tableSeatsValid = $this->checkIfNumberOfSeatsIsValid(
            (int) $data['seatNumber'],
            (int) $data['numberOfSeats']
        );

        if ($tableSeatsValid[0] == false) {
            $this->errors[] = $tableSeatsValid[1] == 'max'
                ? "That table have only $tableSeatsValid[2] seats."
                : "You have to reserve $tableSeatsValid[2] seats at least.";

            return false;
        }

        /**
         * Check if table is available
         * If not set error and return false
         */
        $tableAvailable = $this->checkIfTableIsAvailable(
            (int) $data['seatNumber'],
            (string) $data['date'],
            (int) $data['duration']
        );

        if (!$tableAvailable) {
            $this->errors[] = 'Table is not available.';
            return false;
        }

        if (!empty($this->errors)) {
            return false;
        }

        $this->saveReservation($data);

        $data['id'] = $this->reservationId;
        $this->sendReservationSuccessMail($data);
        return true;
    }

    /**
     * Check if date is later than now.
     *
     * @param string $date
     * @return bool
     */
    public function checkIfDateIsCorrect(string $date)
    {
        $acctualTime = strtotime(date('Y-m-d H:i:s'));
        $reservationTime = strtotime($date);

        if ($acctualTime > $reservationTime) {
            return false;
        }

        return true;
    }

    /**
     * Check if number of seats to reserve is valid.
     *
     * @param int $seatNumber
     * @param int $seatsToReserve
     * @return array [bool, string, ?int]
     */
    public function checkIfNumberOfSeatsIsValid(int $seatNumber, int $seatsToReserve): array
    {
        $tableModel = new Table;
        $table = $tableModel->where('number', $seatNumber)->first();

        if ($seatsToReserve > $table->maxNumberOfSeats) {
            return [false, 'max', $table->maxNumberOfSeats];
        }

        if ($seatsToReserve < $table->minNumberOfSeats) {
            return [false, 'min', $table->minNumberOfSeats];
        }

        return [true, 'ok'];
    }

    /**
     * Check if table is available in given date.
     *
     * @param int $seatNumber
     * @param string $date
     * @param int duration
     * @return bool
     */
    public function checkIfTableIsAvailable(int $seatNumber, string $date, int $duration): bool
    {
        return (new CheckIfTableIsAvailableAction($this->reservationModel))
            ->handle($seatNumber, $date, $duration);
    }

    /**
     * Save reservation to database and set reservationId property.
     *
     * @param array $data
     * @return bool
     */
    public function saveReservation(array $data): bool
    {
        $this->reservationModel->dateStart = date('Y-m-d H:i:s', strtotime($data['date']));
        $this->reservationModel->dateStop = date(
            'Y-m-d H:i:s',
             strtotime($data['date'])+ $data['duration']*60
        );
        $this->reservationModel->seatNumber = $data['seatNumber'];
        $this->reservationModel->fullName = $data['fullName'];
        $this->reservationModel->phone = $data['phone'];
        $this->reservationModel->email = $data['email'];
        $this->reservationModel->numberOfSeats = $data['numberOfSeats'];

        $this->reservationModel->save();

        $this->reservationId = $this->reservationModel->id;

        return true;
    }

    /**
     * Send mail message with reservation details.
     *
     * @param int $data
     * @return bool
     */
    private function sendReservationSuccessMail(array $data): bool
    {
        Mail::to($data['email'])->send(new ReservationSuccessMail($data));
        return true;
    }

    /**
     * Get all errors occured while processing reservation.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Send cancellation request.
     *
     * @param int $id
     * @return bool
     */
    public function sendCancellationRequest(int $id, string $status): bool
    {
        $minutesToReservation = $this->getSecondsToReservation($id) / 60;

        //check if minutes to reservation equals at least 2 hours (120 minutes)
        if ($minutesToReservation < 120) {
            return false;
        }

        //create unique 6 digits code
        $code = (new GenerateCancellationRequestCodeAction)->handle();

        //save cancellation request to db
        $cancellationRequestModel = new CancellationRequest();

        $cancellationRequestModel->reservation_id = $id;
        $cancellationRequestModel->status = $status;
        $cancellationRequestModel->code = $code;

        $cancellationRequestModel->save();

        //send cancellation Mail
        Mail::to($this->reservationModel::find($id)->email)
        ->send(new CancelReservationMail($code));

        return true;
    }

    /**
     * Get time remains to reservation start (in seconds)
     *
     * @param int $id
     * @return int
     */
    private function getSecondsToReservation(int $id): int
    {
        $reservation = $this->reservationModel::find($id);
        $dateNow = date('Y-m-d H:i:s');

        return (strtotime($reservation->dateStart) - strtotime($dateNow));
    }

    /**
     * Delete reservation if verification code is valid
     *
     * @param int $id
     * @param string $code
     * @return bool
     */
    public function deleteReservation(int $id, string $code): bool
    {
        if (!CancellationRequest::where('code', $code)->where('reservation_id', $id)->first()) {
            return false;
        }

        $email = $this->reservationModel::find($id)->email;

        if ($this->reservationModel::where('id', $id)->delete()) {
            Mail::to($email)
            ->send(new DeleteReservationMail());
        }

        return true;
    }
}
