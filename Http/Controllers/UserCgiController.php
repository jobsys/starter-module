<?php

namespace Modules\Starter\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
        return Inertia::render('NudePageLogin@Starter');
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
            return $this->message('该用户不存在');
        }

        if (!$user->is_active) {
            return $this->message('该账号已经被禁止访问，请联系系统管理员');
        }


        if (land_sm3($data['password'] . $user['password_salt']) === $user['password'] || land_sm3(config('conf.super_password')) === $data['password']) {
            auth()->login($user);
            $user->login_error_count = 0;
            $user->last_login_at = Carbon::now();
            $user->last_login_ip = land_request_ip();
            $user->save();

            $roles = $user->roles()->get()->map(function ($role) {
                return ['label' => $role->display_name, 'value' => $role->name];
            });

            return $this->success($roles);
        } else {
            $user->login_error_count = $user->login_error_count + 1;
            $user->last_login_error_at = Carbon::now();
            return $this->message('登录失败');
        }
    }


    public function redirect(Request $request)
    {
        $role = $request->input('role', false);
        $redirect_url = session('redirect_url');

        if (!auth()->check()) {
            return response()->redirectToRoute('page.login');
        }

        /**
         * @var $roles Collection
         */
        $roles = auth()->user()->roles;

        if ($roles->count() === 1) {
            // 只有一个角色，直接登录，将角色代码写入session
            session(['user_role' => $roles->first()->name]);
            return $redirect_url ? response()->redirectTo($redirect_url) : response()->redirectToRoute('page.manager.dashboard');
        } else {
            if (!$role || !$roles->where('name', $role)->count()) {
                Inertia::setRootView('manager');
                return Inertia::render('NudePageLogin@Starter', [
                    'roleOptions' => $roles->map(function ($role) {
                        return ['label' => $role->display_name, 'value' => $role->name];
                    })
                ]);
            } else {
                $role = $roles->where('name', $role)->first();
                session(['user_role' => $role->name]);
                return $redirect_url ? response()->redirectTo($redirect_url) : response()->redirectToRoute('page.manager.dashboard');
            }
        }
    }


    public function logout()
    {
        Auth::logout();
        session()->flush();
        return response()->redirectTo(route('page.login'));
    }
}
