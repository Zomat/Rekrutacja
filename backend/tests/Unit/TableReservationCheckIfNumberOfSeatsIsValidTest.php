<?php

namespace Tests\Unit;

use App\Models\Reservation;
use Tests\TestCase;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Artisan;

class TableReservationCheckIfNumberOfSeatsIsValidTest extends TestCase
{
    private $seatNumber = 4;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
        Artisan::call('db:seed --class=TestReservationSeeder');
        $this->service = new ReservationService(new Reservation);
    }

    public function testNumberOfSeatsIsValid()
    {
        $result = $this->service->checkIfNumberOfSeatsIsValid($this->seatNumber, 4);
        $this->assertEquals($result[0], true);
    }

    public function testNumberOfReservedSeatsIsTooHigh()
    {
        $result = $this->service->checkIfNumberOfSeatsIsValid($this->seatNumber, 10);
        $this->assertEquals($result[0], false);
        $this->assertEquals($result[1], 'max');
    }

    public function testNumberOfReservedSeatsIsTooLow()
    {
        $result = $this->service->checkIfNumberOfSeatsIsValid($this->seatNumber, 2);
        $this->assertEquals($result[0], false);
        $this->assertEquals($result[1], 'min');
    }
}
