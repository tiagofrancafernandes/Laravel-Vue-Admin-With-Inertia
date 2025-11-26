<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of users with pagination.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->paginate(15)->appends($request->query());

        return Inertia::render('Resources/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->input('search'),
                'role' => $request->input('role'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request)
    {
        return Inertia::render('Resources/Users/Create', [
            'pageType' => $request->query('type', 'page'),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['user' => $user], 201);
        }

        return redirect()->route('users.show', $user)
            ->with('message', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user, Request $request)
    {
        return Inertia::render('Resources/Users/Show', [
            'user' => $user,
            'pageType' => $request->query('type', 'page'),
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user, Request $request)
    {
        return Inertia::render('Resources/Users/Edit', [
            'user' => $user,
            'pageType' => $request->query('type', 'page'),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Only update password if it's provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        if ($request->wantsJson()) {
            return response()->json(['user' => $user]);
        }

        return redirect()->route('users.show', $user)
            ->with('message', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('message', 'User deleted successfully.');
    }
}
