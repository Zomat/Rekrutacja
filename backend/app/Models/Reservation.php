<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    // === CUSTOM METHODS === //
    public function getDuration(): int
    {
        return (strtotime($this->dateStop) - strtotime($this->dateStart)) / 60;
    }
}
