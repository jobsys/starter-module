<?php

use App\Models\Department;
use Modules\Permission\Services\PermissionService;

if (!function_exists('starter_setup')) {
    /**
     * 获取网站基本信息
     * @return array
     */
    function starter_setup(): array
    {
        $customer = [
            'code' => config('conf.customer_identify'),
            'name' => config('conf.customer_name'),
        ];

        return compact('customer');
    }
}


if (!function_exists('starter_setup_user')) {
    /**
     * 获取用户相关内容 [菜单, 权限, 部门, 个人信息, 是否超级管理员]
     * @return array
     */
    function starter_setup_user(): array
    {

		$service = app(PermissionService::class);

        $user = auth()->user();

        $permissions = $service->getCurrentUserPermissions();
        $departments = $user->isSuperAdmin()
            ? Department::get(['name', 'id'])->toArray()
            : $user->departments()->get(['name', 'id'])->toArray();

        $menus = starter_get_user_menu($user, $permissions);

        $profile = [
            'name' => $user->name,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar ?? [],
        ];
        $is_super_admin = $user->isSuperAdmin();

        return compact('menus', 'permissions', 'profile', 'departments', 'is_super_admin');
    }
}

if (!function_exists('starter_get_user_menu')) {
    /**
     * 获取用户菜单
     * @param $user
     * @param array $permissions
     * @return array
     */
    function starter_get_user_menu($user = null, array $permissions = []): array
    {
        if (!$user && auth()->guest()) {
            return [];
        }

        $user = $user ?? auth()->user();

        $menus = land_config('menus');

        if ($user->isSuperAdmin()) {
            return $menus;
        }

        if (!$permissions || !count($permissions)) {
            $permissions = $user->getAllPermissions()->filter(function ($item) {
                return \Illuminate\Support\Str::startsWith($item->name, 'page.');
            })->pluck('name')->toArray();
        }

        foreach ($menus as $index => $menu) {
            if (empty($menu['page']) && empty($menu['children'])) {
                unset($menus[$index]);
            } else if (!empty($menu['page']) && !in_array($menu['page'], $permissions)) {
                unset($menus[$index]);
            } else if (!empty($menu['children'])) {
                foreach ($menu['children'] as $i => $child) {
                    if (!empty($child['page']) && !in_array($child['page'], $permissions)) {
                        unset($menus[$index]['children'][$i]);
                    }
                }
                if (empty($menus[$index]['children'])) {
                    unset($menus[$index]);
                } else {
                    $menus[$index]['children'] = array_values($menus[$index]['children']);
                }
            }
        }

        return array_values($menus);
    }
}
