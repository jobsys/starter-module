<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 * 记录除了站内 Notification 外的其它渠道消息记录
	 */
	public function up(): void
	{
		Schema::create('messages', function (Blueprint $table) {
			$table->id();
			$table->string('channel')->index()->nullable()->comment('发送渠道');
			$table->string('receiver_type')->index()->comment('消息接受人类型');
			$table->integer('receiver_id')->index()->comment('消息接受人ID');
			$table->text('content')->nullable()->comment('消息内容');
			$table->json('data')->nullable()->comment('消息数据');
			$table->string('status')->nullable()->comment('消息状态');
			$table->string('remark')->nullable()->comment('备注');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('messages');
	}
};
