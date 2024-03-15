<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('sns_users', function (Blueprint $table) {
			$table->id();
			$table->string('snsable_type')->nullable()->index();
			$table->integer('snsable_id')->nullable()->index();
			$table->string('type')->comment('类型:work, official');
			$table->string('sns_id')->index()->comment('第三方ID');
			$table->dateTime('bound_at')->nullable()->comment('绑定时间');
			$table->boolean('is_auto_login')->default(true)->comment('是否自动登录，一般在退出登录后设置为 false');
			$table->timestamps();
		});

	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('sns_users');
	}
};
