<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;

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

        return Inertia::render('Admin/Users/Index', [
            'users' => $query->paginate(20),
        ]);
    }

    public function create(): Response
    {
        $departments = Department::orderedCached();
        $roles = Role::orderedCached();

        return Inertia::render('Admin/Users/Create', [
            'departments' => $departments,
            'roles' => $roles,
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Користувача додано');
    }

    public function show(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load(['role', 'department']);

        $departments = Department::orderedCached();
        $roles = Role::orderedCached();

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'departments' => $departments,
            'roles' => $roles,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Користувача оновлено');
    }
}
