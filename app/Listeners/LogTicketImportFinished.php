<?php

namespace App\Listeners;

use App\Events\TicketImportFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogTicketImportFinished
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
    public function handle(TicketImportFinished $event): void
    {
        //
    }
}
