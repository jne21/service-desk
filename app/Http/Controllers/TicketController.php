<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Http\Resources\TicketChangeResource;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Services\Contracts\TicketChangeLoggerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        //$user = $request->user();
        
        return Inertia::render('Tickets/Index');
    }

    public function create(): Response
    {
        $this->authorize('create', Ticket::class);

        $statuses = TicketStatus::orderedCached();

        return Inertia::render('Tickets/Create', [
            'statuses' => $statuses,
        ]);
    }

    public function store(
        TicketRequest $request,
        TicketChangeLoggerInterface $changeLogger
    ): RedirectResponse {
        $this->authorize('create', Ticket::class);

        $ticket = Ticket::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'department_id' => $request->user()->department_id,
        ]);

        $changeLogger->logUserAction(
            ticket: $ticket,
            user: $request->user(),
            event: TicketChangeLoggerInterface::EVENT_CREATED,
        );

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Заявку створено');
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load(['status', 'user.role', 'department']);
        
        $statuses = TicketStatus::orderedCached();

        $changes = $ticket->changes()
            ->with(['user', 'source'])
            ->latest()
            ->get();

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'statuses' => $statuses,
            'changes' => TicketChangeResource::collection($changes)->resolve(),
            'can' => [
                'delete' => $request->user()->can('delete', $ticket),
            ],
        ]);
    }

    public function update(
        TicketRequest $request,
        Ticket $ticket,
        TicketChangeLoggerInterface $changeLogger
    ): RedirectResponse {
        $this->authorize('update', $ticket);

        $validated = $request->validated();

        $changes = $changeLogger->buildChanges($ticket, $validated);

        $ticket->update($validated);

        if ($changes !== []) {
            $changeLogger->logUserAction(
                ticket: $ticket,
                user: $request->user(),
                event: TicketChangeLoggerInterface::EVENT_UPDATED,
                changes: $changes,
            );
        }

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Заявку оновлено');
    }

    public function destroy(
        Request $request,
        Ticket $ticket,
        TicketChangeLoggerInterface $changeLogger
    ): RedirectResponse {
        $this->authorize('delete', $ticket);

        $ticket->deleteBy($request->user());

        $changeLogger->logUserAction(
            ticket: $ticket,
            user: $request->user(),
            event: TicketChangeLoggerInterface::EVENT_DELETED,
        );

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Заявку видалено.');
    }
}
