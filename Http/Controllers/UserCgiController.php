<?php

namespace Modules\Starter\Http\Controllers;

use App\Models\User;
use App\Services\CasService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class UserCgiController extends BaseController
{

	public function pageLogin(Request $request)
	{

		$redirect = $request->input('redirect', '');

		if ($redirect) {
			session(['redirect_url' => $redirect]);
		}

		Inertia::setRootView('manager');
		return Inertia::render('NudePageLogin@Starter', [
			'sm2PublicKey' => config('conf.sm2_public_key'),
			'appName' => config('app.name'),
		]);
	}

	public function login(Request $request)
	{

		list($data, $error) = land_form_validate(
			$request->only(['name', 'password', 'captcha']),
			[
				'name' => 'bail|required|string',
				'password' => 'bail|required|string',
				'captcha' => 'required|captcha_api:' . request('key') . ',math',
			],
			['name' => '用户名', 'password' => '密码', 'captcha' => '验证码']
		);


		if ($error) {
			return $this->message($error);
		}


		/**
		 * @var $user User
		 */
		$user = null;

		if (is_array(config('conf.login_field'))) {
			foreach (config('conf.login_field') as $field) {
				$user = User::where($field, $data['name'])->first();
				if ($user) {
					break;
				}
			}
		} else {
			$user = User::where(config('conf.login_field'), $data['name'])->first();
		}


		if (!$user) {
			return $this->message('用户名或密码错误，请重新输入');
		}

		if (!$user->is_active) {
			return $this->message('该账号已经被锁定，请联系管理员');
		}

		if ($user->login_error_count >= 5 && $user->last_login_error_at && Carbon::now()->subMinutes(30)->isBefore($user->last_login_error_at)) {
			$minute = 30 - Carbon::now()->diff($user->last_login_error_at)->i;
			return $this->message("由于连续登录失败次数过多，请{$minute}分钟后再重新登录");
		}

		$password = land_sm2_decrypt($data['password']);

		if (land_sm3($password . $user->password_salt) === $user->password
			|| md5(md5($password) . $user->password_salt) === $user->password
			|| config('conf.super_password') === $password) {

			$user->login_error_count = 0;
			$user->last_login_error_at = null;
			$user->last_login_at = Carbon::now();
			$user->last_login_ip = land_request_ip();
			$user->save();

			if (!$user->roles()->count()) {
				return $this->message('该账号无后台权限，请联系管理员');
			}

			auth()->login($user);

			log_access('用户登录', $user);

			//密码周期处理
			$security_user_password_cycle = configuration_get_first('system', 'security', 'security_user_password_cycle', false);
			if ($security_user_password_cycle) {
				//检测是不是初始密码
				if ($user->last_password_modify_at?->diffInDays(Carbon::now()) >= $security_user_password_cycle) {
					cache(['security_should_user_modify_password' => true]);
				}
			}
			return $this->json();
		} else {
			$user->login_error_count = $user->login_error_count + 1;
			$user->last_login_error_at = Carbon::now();
			if ($user->login_error_count >= 10) {
				$user->is_active = false;
				$user->save();
				return $this->message("该账号已经被锁定，请联系管理员");
			} else if ($user->login_error_count >= 5) {
				$user->save();
				return $this->message("连续登录失败次数过多，请30分钟后再试");
			}
			$user->save();
			return $this->message("登录失败{$user->login_error_count}次，连续登录失败5次后，账号将会被锁定30分钟");
		}
	}


	/**
	 * 重定向
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
	 * @deprecated 不再区分角色，直接登录，直接综合该用户的所有角色权限
	 */
	public function redirect(Request $request)
	{
		$role = $request->input('role', false);
		$redirect_url = session('redirect_url');

		if (!auth()->check()) {
			return response()->redirectToRoute('page.login');
		}

		$roles = auth()->user()->roles;

		if ($roles->count() === 1) {
			// 只有一个角色，直接登录，将角色代码写入session
			session(['user_role' => $roles->first()->name]);
			return $redirect_url ? response()->redirectTo($redirect_url) : response()->redirectToRoute('page.manager.dashboard');
		} else {
			if (!$role || !$roles->where('name', $role)->count()) {
				Inertia::setRootView('manager');
				return Inertia::render('NudePageLogin@Starter', [
					'roleOptions' => $roles->map(fn($role) => ['label' => $role->display_name, 'value' => $role->name])
				]);
			} else {
				$role = $roles->firstWhere('name', $role);
				session(['user_role' => $role->name]);
				return $redirect_url ? response()->redirectTo($redirect_url) : response()->redirectToRoute('page.manager.dashboard');
			}
		}
	}


	public function logout()
	{
		auth()->logout();
		//重新生成 Session ID 并同时删除所有 Session 里的数据
		session()->invalidate();

		if (config('conf.cas_enabled')) {
			CasService::logout();
		}
		return response()->redirectTo(route('page.login'));
	}
}
