<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;

use App\Models\Ticket;
use App\Models\TicketSource;
use App\Models\TicketStatus;

class TicketImportService
{
    public function import(TicketSource $source, array $tickets): array
    {
        return DB::transaction(function () use ($source, $tickets) {
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
    }
}