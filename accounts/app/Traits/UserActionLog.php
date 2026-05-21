<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait UserActionLog
{
    public function logAction($userId, $action, $targetType, $targetId, $meta = null)
    {
        DB::table('tbl_user_action_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'meta' => $meta ? json_encode($meta) : null,
            'created_at' => now(),
        ]);
    }
}
