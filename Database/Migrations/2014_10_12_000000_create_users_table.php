<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->unique()->comment('登录名');
            $table->string('work_num')->nullable()->index()->unique()->comment('工号');
            $table->string('nickname')->nullable()->comment('昵称');
            $table->json('avatar')->nullable()->comment('头像');
            $table->string('phone')->index()->nullable()->comment('电话');
            $table->string('email')->index()->nullable()->comment('邮箱');
            $table->string('password')->nullable();
            $table->string('password_salt')->nullable();
            $table->dateTime('email_verified_at')->nullable()->comment('邮件验证发出时间');
            $table->string('email_verified_token')->index()->nullable()->comment('邮箱验证TOKEN');
            $table->dateTime('sms_verified_at')->nullable()->comment('短信验证发出时间');
            $table->string('sms_verified_token')->index()->nullable()->comment('短信验证码');
            $table->integer('login_error_count')->default(0)->comment('连续登录错误次数');
            $table->dateTime('last_login_error_at')->nullable()->comment('上次错误登录时间');
            $table->dateTime('last_login_at')->nullable()->comment('上次登录时间');
            $table->string('last_login_ip')->nullable()->comment('上次登录IP');
            $table->dateTime('last_password_modify_at')->nullable()->comment('上次修改密码时间');
            $table->boolean('is_active')->default(true)->index()->comment('是否激活');
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
