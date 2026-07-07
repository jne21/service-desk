<?php

namespace App\Listeners;

use App\Events\TicketImportStarted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogTicketImportStarted
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TicketImportStarted $event): void
    {
        //
    }
}
