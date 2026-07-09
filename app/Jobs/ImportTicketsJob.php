<?php

namespace App\Jobs;

use App\Models\TicketImport;
use App\Models\TicketSource;
use App\Services\TicketImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class ImportTicketsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;

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

        $lock = Cache::lock(
            "ticket-import-source:{$source->id}",
            300
        );

        $lock->block(30, function () use ($ticketImportService, $source, $ticketImport) {
            $ticketImportService->import(
                source: $source,
                tickets: $this->tickets,
                ticketImport: $ticketImport,
            );
        });
    }
}