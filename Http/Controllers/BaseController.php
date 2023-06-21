<?php

namespace Modules\Starter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Starter\Emnus\State;

class BaseController extends Controller
{
    /**
     * 通用JSON返回
     * @param $result
     * @param $status
     * @return JsonResponse
     */
    public function json($result = null, $status = State::SUCCESS): JsonResponse
    {
        if (is_string($result) && $result == 'validation.captcha') {
            $status = config('starter.messages.STATE_CODE_CAPTCHA_ERROR');
            return response()->json($status);
        }


        if ($status != State::SUCCESS) {
            $status = config("starter.messages.{$status}");
            if ($result) {
                $status['result'] = $result;
            }
            return response()->json($status);
        }

        $status = config("starter.messages.STATE_CODE_SUCCESS");

        return response()->json([
            'status' => $status['status'],
            'result' => $result
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
