<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\IdleSession;
use App\Models\Penalty;
use App\Models\Setting;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $actionsCount = ActivityLog::count();
        $idleCount = IdleSession::count();
        $penaltiesCount = Penalty::count();

        $recentActivities = ActivityLog::with('user')->latest()->limit(20)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'actionsCount', 'idleCount', 'penaltiesCount', 'recentActivities'
        ));
    }

    public function settings(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'idle_timeout_seconds' => 'required|integer|min:1',
                'monitoring_enabled' => 'nullable|in:0,1',
            ]);

            Setting::updateOrCreate(['key' => 'idle_timeout_seconds'], ['value' => $data['idle_timeout_seconds']]);
            Setting::updateOrCreate(['key' => 'monitoring_enabled'], ['value' => $data['monitoring_enabled'] ?? '0']);

            return back()->with('status', 'Settings saved');
        }

        $idle = Setting::get('idle_timeout_seconds', 5);
        $monitoring = Setting::get('monitoring_enabled', 1);

        return view('admin.settings', compact('idle', 'monitoring'));
    }
}

