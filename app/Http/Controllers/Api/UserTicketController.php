<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

use App\Models\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Http\Requests\Api\UserTicketIndexRequest;

class UserTicketController extends Controller
{
    public function index(UserTicketIndexRequest $request): JsonResponse
    {
        $startedAt = microtime(true);

        $user = $request->user();
        $validated = $request->validated();

        $query = Ticket::query()
            ->with([
                'status',
                'department',
                'user',
                'source',
            ])
            ->visibleFor($user)
            ->latest();

        if (! empty($validated['ticket_id'])) {
            $query->where('id', $validated['ticket_id']);
        }

        if (! empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (! empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $perPage = $validated['per_page'] ?? 20;

        $tickets = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'totalTime' => round(microtime(true) - $startedAt, 3),
            'tickets' => TicketResource::collection($tickets->items()),
            'pagination' => [
                'currentPage' => $tickets->currentPage(),
                'perPage' => $tickets->perPage(),
                'total' => $tickets->total(),
                'lastPage' => $tickets->lastPage(),
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
