<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('snapshots', function (Blueprint $table) {
			$table->id();
			$table->morphs('snapshoter');
			$table->morphs('snapshotable');
			$table->json('snapshot')->comment('快照内容');
			$table->dateTime('snapshot_at')->nullable()->comment('快照时间');
			$table->timestamps();
			$table->comment('快照表');
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
