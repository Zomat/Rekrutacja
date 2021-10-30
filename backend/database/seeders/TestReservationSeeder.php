<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Reservation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reservationModel = new Reservation();
        $tomorrow = Carbon::tomorrow();
        $tomorrow = $tomorrow->toDateString();

        $dateToTest = $tomorrow.' 15:00:00';
        $data = [
            'fullName' => 'John Doe',
            'date' => $dateToTest,
            'duration' => 150,
            'seatNumber' => 4,
            'phone' => '123456789',
            'email' => 'email@example.com',
            'numberOfSeats' => '1',
        ];

        $reservationModel->dateStart = date('Y-m-d H:i:s', strtotime($data['date']));
        $reservationModel->dateStop = date('Y-m-d H:i:s', strtotime($data['date']) + $data['duration']*60);
        $reservationModel->seatNumber = $data['seatNumber'];
        $reservationModel->fullName = $data['fullName'];
        $reservationModel->phone = $data['phone'];
        $reservationModel->email = $data['email'];
        $reservationModel->numberOfSeats = $data['numberOfSeats'];

        $reservationModel->save();
    }
}
