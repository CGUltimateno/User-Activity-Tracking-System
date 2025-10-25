@extends('layouts.app')

@section('title','Users')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Users</h1>
        <a href="{{ route('users.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded">Create</a>
    </div>

    <div class="bg-white shadow rounded">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-left">Name</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2">Role</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="border-t">
                        <td class="p-2">{{ $user->name }}</td>
                        <td class="p-2">{{ $user->email }}</td>
                        <td class="p-2">{{ $user->role }}</td>
                        <td class="p-2">
                            <a href="{{ route('users.show', $user) }}" class="text-blue-600 mr-2">View</a>
                            <a href="{{ route('users.edit', $user) }}" class="text-yellow-600 mr-2">Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection

