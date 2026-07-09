<?php

namespace App\Jobs;

use App\Models\TicketImport;
use App\Models\TicketSource;
use App\Services\TicketImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportTicketsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $ticketSourceId,
        public int $ticketImportId,
        public array $tickets,
    ) {
    }

    public function handle(TicketImportService $ticketImportService): void
    {
        $source = TicketSource::query()->findOrFail($this->ticketSourceId);

        $ticketImport = TicketImport::query()->findOrFail($this->ticketImportId);

        $ticketImportService->import(
            source: $source,
            tickets: $this->tickets,
            ticketImport: $ticketImport,
        );
    }
}