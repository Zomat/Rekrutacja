<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ReservationService;
use App\Http\Requests\GetReservationsRequest;
use App\Http\Requests\CreateReservationRequest;
use App\Http\Requests\DeleteReservationRequest;
use App\Http\Requests\UpdateReservationRequest;

class ReservationController extends Controller
{
    private $service;

    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    /**
     * Returns a list of all reservations for the given date.
     *
     * @param ReservationRequest $request
     * @return JsonResponse
    */
    public function index(GetReservationsRequest $request)
    {
        $data = $request->validated();
        $reservations = $this->service
            ->getAllReservationsByDay(date('Y-m-d', strtotime($data['date'])));

        return response()->json(['bookings' => $reservations], 200);
    }

    /**
     * Creates a new reservation for a table.
     *
     * @param ReservationRequest $request
     * @return JsonResponse
    */
    public function store(CreateReservationRequest $request)
    {
        $data = $request->validated();
        $this->service->makeReservation($data);

        if (!empty($this->service->getErrors())) {
            return response()->json(['errors' => $this->service->getErrors()], 400);
        }

        return response()->json(['reservationId' => $this->service->reservationId], 201);
    }

    /**
     * Requests a cancellation of the reservation
     *
     * @param UpdateReservationRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id, UpdateReservationRequest $request)
    {
        $data = $request->validated();

        if (Reservation::find($id) == null) {
            return response()->json([
                'error' => "Reservation with id: $id not found."
            ], 404);
        }

        if(!$this->service->sendCancellationRequest($id, $data['status'])) {
            return response()->json([
                'error' => "Can't send cancellation request if time to reservation is shorter than 2 hours"
            ], 400);
        }

        return response()->json(['Message' => 'Success'], 200);
    }

    /**
     * Confirms the cancellation request by checking the verification code
     *
     * @param DeleteReservationRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id, DeleteReservationRequest $request)
    {
        $data = $request->validated();

        if (Reservation::find($id) == null) {
            return response()->json([
                'error' => "Reservation with id: $id not found."
            ], 404);
        }

        if(!$this->service->deleteReservation($id, $data['verificationCode'])) {
            return response()->json([
                'error' => "Verification code is invalid."
            ], 400);
        }

        return response()->json(['Message' => 'Success'], 200);
    }
}
