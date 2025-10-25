<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(string $action, ?string $model = null, ?int $recordId = null, array $meta = [])
    {
        try {
            $request = request();
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model' => $model,
                'record_id' => $recordId,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            // swallow errors to avoid breaking user flows
            logger()->error('ActivityLogger error: ' . $e->getMessage());
        }
    }
}
