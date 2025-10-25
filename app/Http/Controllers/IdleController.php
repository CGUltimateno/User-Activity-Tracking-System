<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdleSession;
use App\Models\Penalty;
use App\Services\ActivityLogger;
use Carbon\Carbon;

class IdleController extends Controller
{
    public function event(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:start,first_alert,warning,penalty,end',
            'session_id' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'reason' => 'nullable|string',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        $ip = $request->ip();
        $ua = $request->userAgent();

        if ($data['type'] === 'start') {
            $idle = IdleSession::create([
                'user_id' => $user->id,
                'started_at' => Carbon::now(),
                'ip_address' => $ip,
                'user_agent' => $ua,
            ]);
            ActivityLogger::log('idle_start', null, $idle->id);
            return response()->json(['ok' => true, 'idle_session_id' => $idle->id]);
        }

        if ($data['type'] === 'end') {
            if (! empty($data['session_id'])) {
                $idle = IdleSession::find($data['session_id']);
                if ($idle) {
                    $idle->ended_at = Carbon::now();
                    if ($idle->started_at) {
                        $idle->duration_seconds = $idle->ended_at->diffInSeconds($idle->started_at);
                    }
                    $idle->save();
                    ActivityLogger::log('idle_end', null, $idle->id);
                }
            }
            return response()->json(['ok' => true]);
        }

        if ($data['type'] === 'penalty') {
            $idle = null;
            if (! empty($data['session_id'])) {
                $idle = IdleSession::find($data['session_id']);
            }

            $pen = Penalty::create([
                'user_id' => $user->id,
                'reason' => $data['reason'] ?? 'Inactivity penalty',
                'count' => 1,
                'applied_at' => Carbon::now(),
            ]);

            if ($idle) {
                $idle->penalty_count = ($idle->penalty_count ?? 0) + 1;
                $idle->save();
            }

            ActivityLogger::log('penalty', null, $pen->id, ['reason' => $pen->reason]);

            return response()->json(['ok' => true, 'logout' => true]);
        }
        ActivityLogger::log('idle_'.$data['type'], null, null, ['note' => $data]);
        return response()->json(['ok' => true]);
    }
}

