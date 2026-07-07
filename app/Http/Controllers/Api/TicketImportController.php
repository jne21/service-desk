<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Models\TicketSource;
use App\Models\TicketImport;
use App\Models\TicketImportStatus;

use App\Services\TicketImportService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TicketImportRequest;
use App\Http\Controllers\Concerns\ApiResponses;


class TicketImportController extends Controller
{
    use ApiResponses;

    public function show(Request $request, TicketImport $ticketImport): JsonResponse
    {
        $source = $request->attributes->get('ticket_source');

        if ($ticketImport->ticket_source_id !== $source->id) {
            return $this->errorResponse('Імпорт не знайдено.', 404);
        }

        $ticketImport->load(['source', 'status']);

        return $this->successResponse([
            'import' => [
                'id' => $ticketImport->id,
                'source' => [
                    'id' => $ticketImport->source->id,
                    'code' => $ticketImport->source->code,
                    'name' => $ticketImport->source->name,
                ],
                'status' => [
                    'id' => $ticketImport->status->id,
                    'code' => $ticketImport->status->code,
                    'name' => $ticketImport->status->name,
                    'isFinal' => $ticketImport->status->is_final,
                ],
                'ticketsCount' => $ticketImport->tickets_count,
                'createdCount' => $ticketImport->created_count,
                'updatedCount' => $ticketImport->updated_count,
                'failedCount' => $ticketImport->failed_count,
                'error' => $ticketImport->error_message,
                'startedAt' => $ticketImport->started_at?->toDateTimeString(),
                'finishedAt' => $ticketImport->finished_at?->toDateTimeString(),
                'createdAt' => $ticketImport->created_at?->toDateTimeString(),
            ],
        ]);
    }

    public function store(
        TicketImportRequest $request,
        TicketImportService $ticketImportService
    ): JsonResponse
    {
        /** @var TicketSource $source */
        $source = $request->attributes->get('ticket_source');

        if (! $source) {
            return $this->errorResponse('Ticket source is not resolved.', 401);

            //return response()->json([
            //    'success' => false,
            //    'message' => 'Ticket source was not resolved.',
            //], 401, [], JSON_UNESCAPED_UNICODE);
            //abort(401, 'Ticket source was not resolved.');
        }

        try {
            $tickets = $request->validated('tickets');

            $ticketImport = TicketImport::create([
                'ticket_source_id' => $source->id,
                'status_id' => TicketImportStatus::idByCode(TicketImportStatus::CODE_PROCESSING),
                'tickets_count' => count($tickets),
                'started_at' => now(),
            ]);

            try {
                $result = $ticketImportService->import(
                    source: $source,
                    tickets: $tickets,
                    ticketImport: $ticketImport,
                );
            } catch (\Throwable $e) {
                return $this->errorResponse(
                    'Помилка під час імпорту заявок.',
                    500
                );
            }

        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Помилка під час імпорту заявок.',
                500
            );
        }

        return $this->successResponse([
            'importId' => $ticketImport->id,
            'source' => [
                'id' => $source->id,
                'code' => $source->code,
                'name' => $source->name,
            ],
            'created' => $result['created'],
            'updated' => $result['updated'],
        ]);
    }
}