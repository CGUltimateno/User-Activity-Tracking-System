@extends('layouts.app')

@section('title','Edit User')

@section('content')
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Edit User</h2>
        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="block text-sm">Name</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Email</label>
                <input name="email" value="{{ old('email', $user->email) }}" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Password (leave blank to keep)</label>
                <input name="password" type="password" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Confirm Password</label>
                <input name="password_confirmation" type="password" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Role</label>
                <select name="role" class="w-full border p-2">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm">Avatar</label>
                <input type="file" name="avatar" class="w-full" />
            </div>
            <div class="flex justify-end">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
@endsection

