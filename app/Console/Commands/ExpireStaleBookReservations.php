<?php

namespace App\Console\Commands;

use App\Models\BookReservation;
use Illuminate\Console\Command;

class ExpireStaleBookReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Cancel expired OPAC book reservations and release on-hold copies';

    public function handle(): int
    {
        $count = BookReservation::runExpiry();

        $this->info("Expired {$count} reservation(s).");

        return self::SUCCESS;
    }
}
