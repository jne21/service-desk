<?php

namespace App\Listeners;

use App\Events\TicketImportFinished;
use Illuminate\Support\Facades\Log;

class LogTicketImportFinished
{
    public function handle(TicketImportFinished $event): void
    {
        Log::channel('ticket_import')->info('Ticket import finished', [
            'source_id' => $event->source->id,
            'source_code' => $event->source->code,
            'created' => $event->created,
            'updated' => $event->updated,
            'duration' => $event->duration,
        ]);
    }
}