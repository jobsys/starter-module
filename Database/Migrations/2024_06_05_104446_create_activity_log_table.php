<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('log_name')->nullable();
			$table->string('description')->index();
			$table->nullableMorphs('subject', 'subject');
			$table->string('event')->nullable();
			$table->nullableMorphs('causer', 'causer');
			$table->json('properties')->nullable();
			$table->uuid('batch_uuid')->nullable();
			$table->timestamps();
			$table->index('log_name');
			$table->comment('日志表');
		});
	}

	public function down()
	{
		Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
	}
};
