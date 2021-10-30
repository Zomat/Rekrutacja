<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Table::truncate();
        $jsonTablesString = file_get_contents(base_path('seats.json'));
        $tables = json_decode($jsonTablesString, true);

        foreach($tables['tables'] as $table) {
            $tableModel = new Table;

            $tableModel->number = $table['number'];
            $tableModel->minNumberOfSeats = $table['minNumberOfSeats'];
            $tableModel->maxNumberOfSeats = $table['maxNumberOfSeats'];

            $tableModel->save();
        }
    }
}
