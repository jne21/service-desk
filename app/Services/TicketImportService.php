<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Models\Ticket;
use App\Models\TicketSource;
use App\Models\TicketStatus;
use App\Models\TicketImport;
use App\Models\TicketImportStatus;

use App\Events\TicketImportStarted;
use App\Events\TicketImportFinished;
use app\Events\TicketImportFailed;

class TicketImportService
{
    public function import(TicketSource $source, array $tickets, TicketImport $ticketImport): array
    {
        $startedAt = microtime(true);
        $ticketImport->update([
            'status_id' => TicketImportStatus::idByCode(TicketImportStatus::CODE_PROCESSING),
            'started_at' => now(),
        ]);
            
        TicketImportStarted::dispatch(
            source: $source,
            ticketsCount: count($tickets),
        );

        try {
            $result = DB::transaction(function () use ($source, $tickets) {

                $defaultStatusId = TicketStatus::idByCode(TicketStatus::CODE_NEW);

                $created = 0;
                $updated = 0;

                foreach ($tickets as $item) {
                    $ticket = Ticket::updateOrCreate(
                        [
                            'source_id' => $source->id,
                            'external_id' => $item['ticket_id'],
                        ],
                        [
                            'title' => $item['title'],
                            'description' => $item['description'] ?? null,
                            'status_id' => $item['status_id'] ?? $defaultStatusId,
                            'user_id' => $item['user_id'] ?? null,
                            'department_id' => $item['department_id'] ?? null,
                        ]
                    );

                    if ($ticket->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }

                return [
                    'created' => $created,
                    'updated' => $updated,
                ];
            });

            $ticketImport->update([
                'status_id' => TicketImportStatus::idByCode(TicketImportStatus::CODE_FINISHED),
                'created_count' => $result['created'],
                'updated_count' => $result['updated'],
                'failed_count' => 0,
                'finished_at' => now(),
            ]);

            TicketImportFinished::dispatch(
                source: $source,
                created: $result['created'],
                updated: $result['updated'],
                duration: round(microtime(true) - $startedAt, 3),
            );

            return $result;
        } catch (\Throwable $e) {
            $ticketImport->update([
                'status_id' => TicketImportStatus::idByCode(TicketImportStatus::CODE_FAILED),
                'failed_count' => count($tickets),
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            TicketImportFailed::dispatch(
                source: $source,
                ticketsCount: count($tickets),
                duration: round(microtime(true) - $startedAt, 3),
                error: $e->getMessage(),
            );

            throw $e;
        }
    }
}