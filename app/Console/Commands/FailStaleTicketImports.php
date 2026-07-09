<?php

namespace App\Console\Commands;

use App\Models\TicketImport;
use App\Models\TicketImportStatus;
use Illuminate\Console\Command;

class FailStaleTicketImports extends Command
{
    protected $signature = 'ticket-imports:fail-stale {--minutes=15}';

    protected $description = 'Mark stale processing ticket imports as failed';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');

        $processingStatusId = TicketImportStatus::idByCode(
            TicketImportStatus::CODE_PROCESSING
        );

        $failedStatusId = TicketImportStatus::idByCode(
            TicketImportStatus::CODE_FAILED
        );

        $affected = TicketImport::query()
            ->where('status_id', $processingStatusId)
            ->whereNotNull('started_at')
            ->where('started_at', '<', now()->subMinutes($minutes))
            ->update([
                'status_id' => $failedStatusId,
                'finished_at' => now(),
                'error_message' => 'Import processing timeout. Queue worker may have stopped unexpectedly.',
                'updated_at' => now(),
            ]);

        $this->info("Stale ticket imports marked as failed: {$affected}");

        return self::SUCCESS;
    }
}