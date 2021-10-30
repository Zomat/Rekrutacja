<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Reservation;

class checkIfTableIsAvailableAction
{
    private $reservationModel;

    public function __construct(Reservation $reservationModel)
    {
        $this->reservationModel = $reservationModel;
    }

    /**
     * Check if table is available in given date.
     *
     * @param int $seatNumber
     * @param string $date
     * @param int duration
     * @return bool
     */
    public function handle(int $seatNumber, string $date, int $duration): bool
    {
        $startDate = date('Y-m-d H:i:s', strtotime($date));
        $stopDate = date('Y-m-d H:i:s', strtotime($date) + $duration*60);

        $conflictReservation = $this->reservationModel->where('seatNumber', $seatNumber)
            ->where(function($query) use ($startDate, $stopDate) {
                $query->where(function($query) use ($startDate, $stopDate) {
                    $query->whereBetween('dateStart', [$startDate, $stopDate])
                        ->orWhereBetween('dateStop', [$startDate, $stopDate]);
                })
                ->orWhere(function($query) use ($startDate, $stopDate){
                    $query->where('dateStart', '<=', $startDate)
                        ->where('dateStop', '>=', $stopDate);
                });
            })
        ->first();

        if ($conflictReservation == null) {
            return true;
        };

        return false;
    }
}
