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
use App\Events\TicketImportFailed;
use App\Services\Contracts\TicketChangeLoggerInterface;

class TicketImportService
{
    public function __construct(
        private readonly TicketChangeLoggerInterface $changeLogger,
        private readonly TicketRealtimeNotifier $realtimeNotifier
    ) {
        //
    }

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
            $realtimeEvents = [];

            $result = DB::transaction(function () use ($source, $tickets, &$realtimeEvents) {
                $defaultStatusId = TicketStatus::idByCode(TicketStatus::CODE_NEW);
                $restoredStatusId = TicketStatus::idByCode(TicketStatus::CODE_RESTORED);

                $created = 0;
                $updated = 0;
                $restored = 0;

                foreach ($tickets as $item) {
                    $attributes = [
                        'title' => $item['title'],
                        'description' => $item['description'] ?? null,
                        'status_id' => $item['status_id'] ?? $defaultStatusId,
                        'user_id' => $item['user_id'] ?? null,
                        'department_id' => $item['department_id'] ?? null,
                    ];

                    /** @var Ticket $ticket */
                    $ticket = Ticket::withTrashed()
                        ->where('source_id', $source->id)
                        ->where('external_id', $item['ticket_id'])
                        ->first();

                    if ($ticket) {
                        $oldDepartmentId = $ticket->department_id;

                        if ($ticket->trashed()) {
                            $attributes['status_id'] = $restoredStatusId;
                            $attributes['deleted_by_user_id'] = null;

                            $changes = $this->changeLogger->buildChanges($ticket, $attributes);

                            $ticket->restore();
                            $ticket->forceFill($attributes)->save();

                            $this->changeLogger->logSourceAction(
                                ticket: $ticket,
                                source: $source,
                                event: TicketChangeLoggerInterface::EVENT_RESTORED,
                                changes: $changes,
                            );

                            $realtimeEvents[] = [
                                'type' => TicketRealtimeNotifier::TYPE_RESTORED,
                                'ticket_id' => $ticket->id,
                            ];

                            $restored++;
                        } else {
                            $changes = $this->changeLogger->buildChanges($ticket, $attributes);

                            $ticket->forceFill($attributes)->save();

                            if ($changes !== []) {
                                $this->changeLogger->logSourceAction(
                                    ticket: $ticket,
                                    source: $source,
                                    event: TicketChangeLoggerInterface::EVENT_UPDATED,
                                    changes: $changes,
                                );

                                $realtimeEvents[] = [
                                    'type' => TicketRealtimeNotifier::TYPE_UPDATED,
                                    'ticket_id' => $ticket->id,
                                    'old_department_id' => $oldDepartmentId,
                                ];
                            }

                            $updated++;
                        }

                        continue;
                    }                    

                    Ticket::create([
                        'source_id' => $source->id,
                        'external_id' => $item['ticket_id'],
                        ...$attributes,
                    ]);

                    $this->changeLogger->logSourceAction(
                        ticket: $ticket,
                        source: $source,
                        event: TicketChangeLoggerInterface::EVENT_CREATED,
                    );

                    $realtimeEvents[] = [
                        'type' => TicketRealtimeNotifier::TYPE_CREATED,
                        'ticket_id' => $ticket->id,
                    ];

                    $created++;
                }

                return [
                    'created' => $created,
                    'updated' => $updated,
                    'restored' => $restored,
                ];
            });

            $this->dispatchRealtimeEvents($realtimeEvents);
            
            $ticketImport->update([
                'status_id' => TicketImportStatus::idByCode(TicketImportStatus::CODE_FINISHED),
                'created_count' => $result['created'],
                'updated_count' => $result['updated'],
                'restored_count' => $result['restored'],
                'failed_count' => 0,
                'finished_at' => now(),
            ]);

            TicketImportFinished::dispatch(
                source: $source,
                created: $result['created'],
                updated: $result['updated'],
                restored: $result['restored'],
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