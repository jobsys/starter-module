<?php

if (!function_exists('log_access')) {
    /**
     * @param string $action 动作
     * @param string $object 对象
     * @param string $effect 结果
     * @param int $user_id 用户ID
     * @param string $user_type 用户类型
     * @param string $brief 摘要
     * @return void
     */
    function log_access(string $action, string $object = '', string $effect = '', string $brief = '', int $user_id = 0, string $user_type = 'user'): void
    {

        if (!$user_id) {
            $user_id = Auth::id();
        }
        DB::table('access_logs')->insert([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'ip' => \Illuminate\Support\Facades\Request::header('x-forwarded-for') ?? \Illuminate\Support\Facades\Request::ip(),
            'object' => $object,
            'brief' => $brief,
            'action' => $action,
            'effect' => $effect,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
