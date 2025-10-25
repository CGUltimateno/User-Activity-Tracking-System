@extends('layouts.app')

@section('title','Admin Dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="p-4 bg-white rounded shadow">Total users<br/><span class="text-2xl font-bold">{{ $totalUsers }}</span></div>
        <div class="p-4 bg-white rounded shadow">Actions<br/><span class="text-2xl font-bold">{{ $actionsCount }}</span></div>
        <div class="p-4 bg-white rounded shadow">Idle sessions<br/><span class="text-2xl font-bold">{{ $idleCount }}</span></div>
        <div class="p-4 bg-white rounded shadow">Penalties<br/><span class="text-2xl font-bold">{{ $penaltiesCount }}</span></div>
    </div>

    <div class="bg-white rounded shadow p-4">
        <h2 class="font-bold mb-2">Recent activities</h2>
        <ul>
            @foreach($recentActivities as $act)
                <li class="border-t py-2">[{{ $act->created_at->toDateTimeString() }}] {{ $act->user?->name ?? 'System' }} - {{ $act->action }} @if($act->model) ({{ $act->model }} #{{ $act->record_id }}) @endif</li>
            @endforeach
        </ul>
    </div>
@endsection

