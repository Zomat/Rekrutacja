<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CancellationRequest;

class GenerateCancellationRequestCodeAction
{
    /**
     * Generate unique 6 digit code
     *
     * @return integer
     */
    public function handle(): int
    {
        do {
            $code = random_int(100000, 999999);
        } while (CancellationRequest::where("code", "=", $code)->first());

        return $code;
    }
}
