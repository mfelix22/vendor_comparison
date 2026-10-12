<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(static function ($request, $next) {
                if (!auth()->user()?->isAdmin()) {
                    abort(403, 'Admin access required.');
                }
                return $next($request);
            }),
        ];
    }

    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'unique:users,email'],
            'role'               => ['required', 'in:creator,supervisor,procurement,manager,viewer,admin,controller'],
            'password'           => ['required', 'string', 'min:8'],
            'has_costing_access' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'role'               => $data['role'],
            'password'           => Hash::make($data['password']),
            'has_costing_access' => $request->boolean('has_costing_access'),
        ]);

        return back()->with('success', "User {$data['name']} created.");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'role'               => ['required', 'in:creator,supervisor,procurement,manager,viewer,admin,controller'],
            'password'           => ['nullable', 'string', 'min:8'],
            'has_costing_access' => ['nullable', 'boolean'],
        ]);

        $user->name = $data['name'];
        $user->role = $data['role'];
        $user->has_costing_access = $request->boolean('has_costing_access');
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', "User {$user->name} updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}
