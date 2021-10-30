<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TableReservationCheckIfTableIsAvailableTest extends TestCase
{
    private $postRequestData = [
        'fullName' => 'John Doe',
        'date' => '',
        'duration' => null,
        'seatNumber' => 4,
        'phone' => '123456789',
        'email' => 'email@example.com',
        'numberOfSeats' => '1'
    ];

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
        Artisan::call('db:seed --class=TestReservationSeeder');
    }

    private function createPostRequest(array $data)
    {
        return $this->withHeaders([
            'X-Header' => 'Value',
            'Accept' => 'application/json'
        ])->post('/api/reservations', $data);
    }

    // public function test_requestedReservationIsBeforeConflictReservation()
    // {
    //     $data = $this->postRequestData;
    //     $data['date'] = '2021-11-13T13:00:00';
    //     $data['duration'] = '60';

    //     $response = $this->createPostRequest($data);
    //     $response->assertStatus(200);
    // }

    // public function test_requestedReservationIsAfterConflictReservation()
    // {
    //     $data = $this->postRequestData;
    //     $data['date'] = '2021-11-13T18:00:00';
    //     $data['duration'] = '90';

    //     $response = $this->createPostRequest($data);
    //     $response->assertStatus(200);
    // }

    // public function test_requestedReservationIsOnConflictReservationStart()
    // {
    //     $data = $this->postRequestData;
    //     $data['date'] = '2021-11-13T14:00:00';
    //     $data['duration'] = '60';

    //     $response = $this->createPostRequest($data);
    //     $response->assertStatus(422);
    // }

    // public function test_requestedReservationIsBetweenConflictReservation()
    // {
    //     $data = $this->postRequestData;
    //     $data['date'] = '2021-11-13T15:15:00';
    //     $data['duration'] = '45';

    //     $response = $this->createPostRequest($data);
    //     $response->assertStatus(422);
    // }

    // public function test_requestedReservationIsOnConflictReservationStop()
    // {
    //     $data = $this->postRequestData;
    //     $data['date'] = '2021-11-13T17:00:00';
    //     $data['duration'] = '120';

    //     $response = $this->createPostRequest($data);
    //     $response->assertStatus(422);
    // }
}
