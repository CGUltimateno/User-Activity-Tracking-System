<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);
        ActivityLogger::log('read', 'User', null, ['action' => 'list']);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'avatar' => 'nullable|image|max:2048',
            'role' => 'required|in:user,admin',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        ActivityLogger::log('create', 'User', $user->id, ['email' => $user->email]);

        return redirect()->route('users.index');
    }

    public function show(User $user)
    {
        ActivityLogger::log('read', 'User', $user->id);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'avatar' => 'nullable|image|max:2048',
            'role' => 'required|in:user,admin',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        ActivityLogger::log('update', 'User', $user->id);

        return redirect()->route('users.show', $user);
    }

    public function destroy(User $user)
    {
        ActivityLogger::log('delete', 'User', $user->id);
        // delete avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();
        return redirect()->route('users.index');
    }

    public function downloadAvatar(User $user)
    {
        if (! $user->avatar || ! Storage::disk('public')->exists($user->avatar)) {
            abort(404);
        }
        ActivityLogger::log('download', 'User', $user->id, ['field' => 'avatar']);
        return Storage::disk('public')->download($user->avatar, $user->name . '_avatar');
    }
}

