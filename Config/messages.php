<?php

return [
    'STATE_CODE_SUCCESS' => ['status' => 'SUCCESS', 'result' => '请求成功'],
    'STATE_CODE_FAIL' => ['status' => 'FAIL', 'result' => '请求失败'],
    'STATE_CODE_NOT_LOGIN' => ['status' => 'NOT_LOGIN', 'result' => '请先登录'],
    'STATE_CODE_DB_FAIL' => ['status' => 'DB_FAIL', 'result' => '数据保存出错'],
    'STATE_CODE_INVALID_PARAMETERS' => ['status' => 'PARAMETER_INVALID', 'result' => '请求参数有误'],
    'STATE_CODE_NOT_FOUND' => ['status' => 'NOT_FOUND', 'result' => '请求的内容不存在'],
    'STATE_CODE_NOT_ALLOWED' => ['status' => 'NOT_ALLOWED', 'result' => '没有操作权限'],
    'STATE_CODE_DUPLICATION' => ['status' => 'DUPLICATION', 'result' => '请勿重复操作'],
    'STATE_CODE_USER_INVALID' => ['status' => 'USER_INVALID', 'result' => '账号异常，请联系客服'],
    'STATE_CODE_USER_INFO_INCOMPLETE' => ['status' => 'USER_INFO_INCOMPLETE', 'result' => '个人信息完善'],
    'STATE_CODE_TOO_FREQUENTLY' => ['status' => 'TOO_FREQUENTLY', 'result' => '操作太频繁，请稍候再试'],
    'STATE_CODE_CAPTCHA_ERROR' => ['status' => 'CAPTCHA_ERROR', 'result' => '验证码错误'],
    'STATE_CODE_VERIFY_ERROR' => ['status' => 'VERIFY_ERROR', 'result' => '认证错误'],
    'STATE_CODE_MOBILE_ERROR' => ['status' => 'MOBILE_ERROR', 'result' => '手机号码格式错误'],
    'STATE_CODE_EMAIL_ERROR' => ['status' => 'EMAIL_ERROR', 'result' => '电子邮箱格式错误'],
    'STATE_CODE_RISKY_CONTENT' => ['status' => 'RISKY_CONTENT', 'result' => '内容中包含敏感词汇'],
    'STATE_CODE_RISKY_IMAGE' => ['status' => 'RISKY_IMAGE', 'result' => '图片包含敏感信息'],
];
