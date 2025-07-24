<?php

namespace Modules\Starter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Starter\Enums\State;

class BaseController extends Controller
{
	/**
	 * 通用JSON返回
	 * @param null $result
	 * @param string $status
	 * @param string $result_key
	 * @return JsonResponse
	 */
	public function json($result = null, string $status = State::SUCCESS, string $result_key = 'result'): JsonResponse
	{
		if (is_string($result) && $result == 'validation.captcha') {
			$status = config('starter.messages.STATE_CODE_CAPTCHA_ERROR');
			return response()->json($status);
		}


		if ($status != State::SUCCESS) {
			$status = config("starter.messages.{$status}");
			if ($result) {
				$status[$result_key] = $result;
			}
			return response()->json($status);
		}

		$status = config("starter.messages.STATE_CODE_SUCCESS");

		return response()->json([
			'status' => $status['status'],
			$result_key => $result
		]);
	}

	/**
	 * 错误信息 JSON 返回
	 * @param $message
	 * @return JsonResponse
	 */
	public function message($message): JsonResponse
	{
		return response()->json([
			'status' => config("starter.messages.STATE_CODE_FAIL.status"),
			'result' => $message
		]);
	}


	/**
	 * 成功信息 JSON 返回
	 * @param $result
	 * @return JsonResponse
	 */
	public function success($result): JsonResponse
	{
		return response()->json([
			'status' => config("starter.messages.STATE_CODE_SUCCESS.status"),
			'result' => $result
		]);
	}
}
