<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')
            ->where('company_id', $request->user()->company_id)
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', ['roles' => $this->roleOptions()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        DB::transaction(function () use ($request): void {
            $user = User::create([
                'company_id' => $request->user()->company_id,
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'is_active' => $request->boolean('is_active'),
            ]);

            $user->syncRoles($request->validated('roles', []));
        });

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user->load('roles'),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        DB::transaction(function () use ($request, $user): void {
            $user->fill([
                'name' => $request->validated('name', $user->name),
                'email' => $request->validated('email', $user->email),
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($request->filled('password')) {
                $user->password = Hash::make($request->validated('password'));
            }

            $user->save();
            $user->syncRoles($request->validated('roles', []));
        });

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    /**
     * @return array<string, string>
     */
    private function roleOptions(): array
    {
        return Role::orderBy('name')->pluck('name', 'name')->all();
    }
}
