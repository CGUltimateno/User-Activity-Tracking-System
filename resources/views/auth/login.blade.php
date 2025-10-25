@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Login</h2>
        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="mb-3">
                <label class="block text-sm">Email</label>
                <input name="email" value="{{ old('email') }}" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm">Password</label>
                <input name="password" type="password" class="w-full border p-2" />
            </div>
            <div class="flex justify-end">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Login</button>
            </div>
        </form>
    </div>
@endsection

