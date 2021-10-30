<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Table;
use App\Models\Reservation;
use App\Actions\checkIfTableIsAvailableAction;

class TableService{

    private $tableModel;

    public function __construct(Table $tableModel)
    {
        $this->tableModel = $tableModel;
    }

    /**
     * Get a list of all available tables based on given time and number of people.
     *
     * @param string $status
     * @param int $minSeats
     * @param string $startDate
     * @param int $duration
     * @return Array
     */
    public function getAvailableTables(string $status, int $minSeats, string $startDate, int $duration)
    {
        $tables = Table::where('minNumberOfSeats', '<=', $minSeats)
            ->where('maxNumberOfSeats', '>=', $minSeats)
            ->get();

        $tablesArray = [];

        $action = new checkIfTableIsAvailableAction(new Reservation);

        if ($status == 'free') {
            foreach ($tables as $table) {
                $tableAvailable = $action->handle(
                    (int) $table->number,
                    (string) $startDate,
                    (int) $duration
                );

                if ($tableAvailable) {
                    $tablesArray[] = [
                        'number' => $table->number,
                        'minNumberOfSeats' => $table->minNumberOfSeats,
                        'maxNumberOfSeats' => $table->maxNumberOfSeats,
                    ];
                }
            }
        }

        return $tablesArray;
    }

}
