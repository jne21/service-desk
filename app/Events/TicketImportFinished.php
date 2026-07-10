<?php

namespace App\Events;

use App\Models\TicketSource;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketImportFinished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TicketSource $source,
        public int $created,
        public int $updated,
        public int $restored,
        public float $duration,
    ) {
    }
}