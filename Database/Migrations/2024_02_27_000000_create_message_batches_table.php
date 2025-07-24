<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * 记录除了站内 Notification 外的其它渠道消息记录
	 */
	public function up(): void
	{
		Schema::create('message_batches', function (Blueprint $table) {
			$table->id();
			$table->integer('creator_id')->nullable()->index()->comment('创建人');
			$table->string('title')->index()->comment('消息标题');
			$table->text('content')->nullable()->comment('消息内容');
			$table->json('channels')->nullable()->comment('发送渠道, database, work, official, etc...');
			$table->string('receiver_type', 100)->index()->comment('消息接受人类型');
			$table->json('receivers')->comment('消息接受人列表');
			$table->string('send_type', 20)->index()->comment('发送类型: immediate, schedule, cron');
			$table->json('send_params')->nullable()->comment('发送参数, cron: cron表达式, schedule: 发送时间');
			$table->string('status', 20)->index()->nullable()->comment('消息队列状态');
			$table->integer('total')->default(0)->nullable()->comment('消息总数');
			$table->integer('sent_count')->default(0)->nullable()->comment('已发送消息数');
			$table->integer('success_count')->default(0)->nullable()->comment('发送成功消息数');
			$table->integer('read_count')->default(0)->nullable()->comment('已读消息数');
			$table->string('remark')->nullable()->comment('备注');
			$table->boolean('is_active')->default(true)->comment('是否启用');
			$table->timestamps();
			$table->comment('消息批次表');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('message_batches');
	}
};
