<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Artisan;

class TableReservationCheckIfTableIsAvailableTest extends TestCase
{
    private $data = [
        'date' => '',
        'duration' => null,
        'seatNumber' => 4,
    ];

    private $tommorowDate;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
        Artisan::call('db:seed --class=TestReservationSeeder');
        $this->service = new ReservationService(new Reservation);

        $this->tommorowDate = Carbon::tomorrow()->toDateString();
    }

    private function createDateFromTime(string $time)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', "$this->tommorowDate $time");
    }

    public function testRequestedReservationIsBeforeConflictReservation()
    {
        $data = $this->data;
        $data['date'] = $this->createDateFromTime('13:00:00');
        $data['duration'] = 60;

        $result = $this->service->checkIfTableIsAvailable(
            $data['seatNumber'],
            $data['date'],
            $data['duration']
        );

        $this->assertTrue($result);
    }

    public function testRequestedReservationIsAfterConflictReservation()
    {
        $data = $this->data;
        $data['date'] = $this->createDateFromTime('18:00:00');
        $data['duration'] = '90';

        $result = $this->service->checkIfTableIsAvailable(
            $data['seatNumber'],
            $data['date'],
            $data['duration']
        );

        $this->assertTrue($result);
    }

    public function testRequestedReservationIsOnConflictReservationStart()
    {
        $data = $this->data;
        $data['date'] = $this->createDateFromTime('14:00:00');
        $data['duration'] = '60';

        $result = $this->service->checkIfTableIsAvailable(
            $data['seatNumber'],
            $data['date'],
            $data['duration']
        );

        $this->assertFalse($result);
    }

    public function testRequestedReservationIsBetweenConflictReservation()
    {
        $data = $this->data;
        $data['date'] = $this->createDateFromTime('15:00:00');
        $data['duration'] = '45';

        $result = $this->service->checkIfTableIsAvailable(
            $data['seatNumber'],
            $data['date'],
            $data['duration']
        );

        $this->assertFalse($result);
    }

    public function testRequestedReservationIsOnConflictReservationStop()
    {
        $data = $this->data;
        $data['date'] = $this->createDateFromTime('17:00:00');
        $data['duration'] = '120';

        $result = $this->service->checkIfTableIsAvailable(
            $data['seatNumber'],
            $data['date'],
            $data['duration']
        );

        $this->assertFalse($result);
    }
}
