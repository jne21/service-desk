<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Ticket;
use App\Models\TicketSource;
use App\Models\TicketStatus;
use App\Events\TicketImportFinished;
use app\Events\TicketImportFailed;

class TicketImportService
{
    public function import(TicketSource $source, array $tickets): array
    {
        $startedAt = microtime(true);

        Log::channel('ticket_import')->info('Ticket import started', [
            'source_id' => $source->id,
            'source_code' => $source->code,
            'tickets_count' => count($tickets),
        ]);

        try {
            $result = DB::transaction(function () use ($source, $tickets) {

                $defaultStatusId = TicketStatus::query()
                    ->where('code', 'new')
                    ->value('id');

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

            TicketImportFinished::dispatch(
                source: $source,
                created: $result['created'],
                updated: $result['updated'],
                duration: round(microtime(true) - $startedAt, 3),
            );

            return $result;
        } catch (\Throwable $e) {
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