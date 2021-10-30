<?php
namespace Tests\Unit;

use DateTime;
use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Artisan;

class TableReservationCheckIfDateIsCorrectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
        Artisan::call('db:seed --class=TestReservationSeeder');
        $this->service = new ReservationService(new Reservation);
    }

    public function testDateIsIncorrect()
    {
        $date = Carbon::yesterday();
        $result = $this->service->checkIfDateIsCorrect($date);

        $this->assertFalse($result);
    }

    public function testDateIsCorrect()
    {
        $datetime = Carbon::tomorrow()->addDay();
        $datetime->format('Y-m-d H:i');
        $result = $this->service->checkIfDateIsCorrect($datetime);

        $this->assertTrue($result);
    }
}

