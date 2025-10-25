@extends('layouts.app')

@section('title', 'User: ' . $user->name)

@section('content')
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">{{ $user->name }}</h2>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Role:</strong> {{ $user->role }}</p>
        @if($user->avatar)
            <p class="mt-3"><img src="{{ asset('storage/' . $user->avatar) }}" alt="avatar" style="max-width:120px" /></p>
            <p><a href="{{ route('users.avatar.download', $user) }}" class="text-blue-600">Download Avatar</a></p>
        @endif
        <div class="mt-4">
            <a href="{{ route('users.edit', $user) }}" class="text-yellow-600 mr-2">Edit</a>
            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
        </div>
    </div>
@endsection

