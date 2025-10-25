@extends('layouts.app')

@section('title','Create User')

@section('content')
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Create User</h2>
        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="block text-sm">Name</label>
                <input name="name" value="{{ old('name') }}" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Email</label>
                <input name="email" value="{{ old('email') }}" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Password</label>
                <input name="password" type="password" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Confirm Password</label>
                <input name="password_confirmation" type="password" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Role</label>
                <select name="role" class="w-full border p-2">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm">Avatar</label>
                <input type="file" name="avatar" class="w-full" />
            </div>
            <div class="flex justify-end">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Create</button>
            </div>
        </form>
    </div>
@endsection

