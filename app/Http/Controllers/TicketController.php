<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Ticket::query()
            ->with('status', 'user', 'department')
            ->visibleFor($user)
            ->latest();

        return Inertia::render('Tickets/Index', [
            'tickets' => $query->paginate(20),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Ticket::class);

        $statuses = TicketStatus::orderedCached();

        return Inertia::render('Tickets/Create', [
            'statuses' => $statuses,
        ]);
    }

    public function store(TicketRequest $request): RedirectResponse
    {
        $this->authorize('create', Ticket::class);
        
        Ticket::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'department_id' => $request->user()->department_id,
        ]);

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Заявку створено');
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load(['status', 'user.role', 'department']);

        $statuses = TicketStatus::orderedCached();

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'statuses' => $statuses,
            'can' => [
                'delete' => $request->user()->can('delete', $ticket),
            ],
        ]);
    }

    public function update(TicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $ticket->update($request->validated());

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Заявку оновлено');
    }

    public function destroy(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->deleteBy($request->user());

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Заявку видалено.');
    }
}
