<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\UserRequest;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()
            ->with('department', 'role')
            ->latest();

        $users = $query->get();

        return Inertia::render('Users/Index', [
            'users' => $query->paginate(20),
        ]);
    }

    public function create(): Response
    {
        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

            $roles = Role::query()
            ->orderBy('id')
            ->get(['id', 'name']);

        return Inertia::render('Users/Create', [
            'departments' => $departments,
            'roles' => $roles,
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->validated()
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Користувача додано');
    }

    public function show(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load(['role', 'department']);

        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $roles = Role::query()
            ->orderBy('id')
            ->get(['id', 'name']);

        return Inertia::render('Users/Show', [
            'user' => $user,
            'departments' => $departments,
            'roles' => $roles,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Користувача оновлено');
    }
}
