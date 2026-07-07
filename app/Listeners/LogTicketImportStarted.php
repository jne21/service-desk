<?php

namespace App\Listeners;

use App\Events\TicketImportStarted;
use Illuminate\Support\Facades\Log;

class LogTicketImportStarted
{
    public function handle(TicketImportStarted $event): void
    {
        Log::channel('ticket_import')->info('Ticket import started', [
            'source_id' => $event->source->id,
            'source_code' => $event->source->code,
            'tickets_count' => $event->ticketsCount,
        ]);
    }
}