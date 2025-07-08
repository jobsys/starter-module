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
		return [
			'customer' => [
				'code' => config('conf.customer_identify'),
				'name' => config('conf.customer_name'),
			],
			'env' => config('app.env'),
		];
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
			'id' => $user->id,
			'name' => $user->name,
			'work_num' => $user->work_num,
			'nickname' => $user->nickname,
			'avatar' => $user->avatar ?? [],
		];
		$is_super_admin = $user->isSuperAdmin();

		return compact('menus', 'permissions', 'profile', 'departments', 'is_super_admin');
	}
}

if (!function_exists('starter_mobile_setup_user')) {
	/**
	 * 获取用户移动端相关内容 [部门, 个人信息, 是否超级管理员]
	 * @return array
	 */
	function starter_mobile_setup_user(): array
	{

		$user = auth()->user();


		$profile = [
			'id' => $user->id,
			'name' => $user->name,
			'work_num' => $user->work_num,
			'nickname' => $user->nickname,
			'avatar' => $user->avatar ?? [],
		];
		$is_super_admin = $user->isSuperAdmin();

		return compact('profile', 'is_super_admin');
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

		if ($user->hasRole(config('conf.role_super'))) {
			return $menus;
		}

		if (!$permissions || !count($permissions)) {
			$permissions = $user->getAllPermissions()->filter(fn($item) => Str::startsWith($item->name, 'page.')
			)->pluck('name')->toArray();
		}


		$filterMenuItems = function (array $menu) use ($permissions, &$filterMenuItems) {
			// 处理子菜单
			if (!empty($menu['children'])) {
				$filtered_children = array_filter(
					array_map($filterMenuItems, $menu['children'])
				);

				// 如果子菜单被完全过滤掉，则移除当前菜单
				if (empty($filtered_children)) {
					return null;
				}

				$menu['children'] = array_values($filtered_children);
				return $menu;
			}

			// 检查页面权限
			if (!empty($menu['page'])) {
				return in_array($menu['page'], $permissions) ? $menu : null;
			}

			// 保留没有page属性的菜单项
			return $menu;
		};

		return array_values(array_filter(array_map($filterMenuItems, $menus)));
	}
}
