<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 * 快照表
	 */
	public function up(): void
	{
		Schema::create('snapshots', function (Blueprint $table) {
			$table->id();
			$table->string('snapshotable_type')->index()->comment('快照模型');
			$table->integer('snapshotable_id')->index()->comment('快照模型ID');
			$table->json('snapshot')->comment('快照内容');
			$table->dateTime('snapshot_at')->nullable()->comment('快照时间');
			$table->integer('snapshot_user_id')->nullable()->index()->comment('操作者ID');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('snapshots');
	}
};
