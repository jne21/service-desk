<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TicketImportRequest;
use App\Models\Ticket;
use App\Models\TicketSource;
use App\Models\TicketStatus;
use Illuminate\Http\JsonResponse;

class TicketImportController extends Controller
{
    public function store(TicketImportRequest $request): JsonResponse
    {
        /** @var TicketSource $source */
        $source = $request->attributes->get('ticket_source');

        if (! $source) {
            abort(401, 'Ticket source was not resolved.');
        }

        $validated = $request->validated();

        $defaultStatusId = TicketStatus::query()
            ->where('code', 'new')
            ->value('id');

        $created = 0;
        $updated = 0;

        foreach ($validated['tickets'] as $item) {
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

        return response()->json([
            'success' => true,
            'source' => [
                'id' => $source->id,
                'code' => $source->code,
                'name' => $source->name,
            ],
            'created' => $created,
            'updated' => $updated,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}