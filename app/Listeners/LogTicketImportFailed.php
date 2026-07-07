<?php

namespace App\Listeners;

use App\Events\TicketImportFailed;
use Illuminate\Support\Facades\Log;

class LogTicketImportFailed
{
    public function handle(TicketImportFailed $event): void
    {
        Log::channel('ticket_import')->error('Ticket import failed', [
            'source_id' => $event->source->id,
            'source_code' => $event->source->code,
            'tickets_count' => $event->ticketsCount,
            'duration' => $event->duration,
            'error' => $event->error,
        ]);
    }
}