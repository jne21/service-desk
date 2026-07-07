<?php

namespace App\Events;

use App\Models\TicketSource;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketImportFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TicketSource $source,
        public int $ticketsCount,
        public float $duration,
        public string $error,
    ) {
    }
}
