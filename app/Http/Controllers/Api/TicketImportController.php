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
use App\Http\Resources\TicketImportResource;


class TicketImportController extends Controller
{
    use ApiResponses;

    public function show(Request $request, TicketImport $ticketImport)
    {
        $source = $request->attributes->get('ticket_source');

        $ticketImport = TicketImport::query()
            ->forSource($source)
            ->whereKey($ticketImport->id)
            ->with(['source', 'status'])
            ->first();

        if ($ticketImport === null) {
            return $this->errorResponse('Імпорт не знайдено.', 404);
        }

        return $this->successResponse([
            'import' => new TicketImportResource($ticketImport),
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