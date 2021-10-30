<?php

namespace App\Http\Controllers;

use App\Http\Requests\TablesRequest;
use App\Models\Reservation;
use App\Models\Table;
use App\Services\ReservationService;
use App\Services\TableService;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Returns a list of all available tables based on the given time and number of people.
     *
     * @param TablesRequest $request
     * @return JsonResponse
     */
    public function index(TablesRequest $request)
    {
        $data = $request->validated();

        $status = $data['status'] ?? 'free';

        $tablesArray = (new TableService(new Table))
            ->getAvailableTables($status, $data['min_seats'], $data['start_date'], $data['duration']);

        return response()->json(['tables' => $tablesArray], 200);
    }

}
