@extends('layouts.app')

@section('title','Settings')

@section('content')
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Monitoring Settings</h2>
        <form method="POST" action="{{ route('admin.settings') }}">
            @csrf
            <div class="mb-3">
                <label class="block text-sm">Idle timeout (seconds)</label>
                <input name="idle_timeout_seconds" value="{{ old('idle_timeout_seconds', $idle) }}" class="w-full border p-2" />
            </div>
            <div class="mb-3">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="monitoring_enabled" value="1" {{ $monitoring ? 'checked' : '' }} />
                    <span class="ml-2">Enable monitoring</span>
                </label>
            </div>
            <div class="flex justify-end">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
@endsection

