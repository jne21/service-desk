<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TicketImportIndexRequest;
use App\Http\Requests\Api\TicketImportRequest;
use App\Http\Resources\TicketImportResource;
use App\Jobs\ImportTicketsJob;
use App\Models\TicketImport;
use App\Models\TicketImportStatus;
use App\Models\TicketSource;
use App\Services\TicketImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class TicketImportController extends Controller
{
    use ApiResponses;

    public function index(TicketImportIndexRequest $request): JsonResponse
    {
        /** @var TicketSource|null $source */
        $source = $request->attributes->get('ticket_source');

        if (! $source) {
            return $this->errorResponse('Ticket source is not resolved.', 401);
        }

        $validated = $request->validated();

        $query = TicketImport::query()
            ->forSource($source)
            ->with(['source', 'status'])
            ->whereBetween('created_at', [
                $validated['date_from'],
                $validated['date_to'],
            ])
            ->orderByDesc('id');

        if (! empty($validated['status'])) {
            $query->where(
                'status_id',
                TicketImportStatus::idByCode($validated['status'])
            );
        }

        $perPage = $validated['per_page']
            ?? (int) config('ticket_imports.list_per_page_default', 20);

        $imports = $query->cursorPaginate($perPage);

        return $this->successResponse([
            'imports' => TicketImportResource::collection($imports->items()),
            'pagination' => [
                'perPage' => $imports->perPage(),
                'nextCursor' => $imports->nextCursor()?->encode(),
                'previousCursor' => $imports->previousCursor()?->encode(),
                'hasMorePages' => $imports->hasMorePages(),
            ],
        ]);
    }

    public function show(Request $request, TicketImport $ticketImport): JsonResponse
    {
        /** @var TicketSource|null $source */
        $source = $request->attributes->get('ticket_source');

        if (! $source) {
            return $this->errorResponse('Ticket source is not resolved.', 401);
        }

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

    public function storeSync(
        TicketImportRequest $request,
        TicketImportService $ticketImportService
    ): JsonResponse {
        /** @var TicketSource|null $source */
        $source = $request->attributes->get('ticket_source');

        if (! $source) {
            return $this->errorResponse('Ticket source is not resolved.', 401);
        }

        try {
            $tickets = $request->validated('tickets');

            $ticketImport = TicketImport::create([
                'ticket_source_id' => $source->id,
                'status_id' => TicketImportStatus::idByCode(TicketImportStatus::CODE_QUEUED),
                'tickets_count' => count($tickets),
            ]);

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

    public function storeAsync(TicketImportRequest $request): JsonResponse
    {
        /** @var TicketSource|null $source */
        $source = $request->attributes->get('ticket_source');

        if (! $source) {
            return $this->errorResponse('Ticket source is not resolved.', 401);
        }

        $tickets = $request->validated('tickets');

        $ticketImport = TicketImport::create([
            'ticket_source_id' => $source->id,
            'status_id' => TicketImportStatus::idByCode(TicketImportStatus::CODE_QUEUED),
            'tickets_count' => count($tickets),
        ]);

        Bus::dispatch(
            new ImportTicketsJob(
                ticketSourceId: $source->id,
                ticketImportId: $ticketImport->id,
                tickets: $tickets,
            )
        );

        return $this->successResponse([
            'importId' => $ticketImport->id,
            'status' => 'queued',
        ]);
    }
}