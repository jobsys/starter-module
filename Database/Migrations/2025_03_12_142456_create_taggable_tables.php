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
		Schema::create('tagging_tags', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('tag_group_id')->index()->unsigned()->nullable();
			$table->integer('creator_id')->nullable()->index()->comment('创建者ID');
			$table->string('slug', 125)->index();
			$table->string('name', 125)->comment('标签名称');
			$table->boolean('suggest')->default(false)->comment('是否推荐');
			$table->integer('count')->unsigned()->default(0)->comment('标签使用次数'); // count of how many times this tag was used
			$table->string('color', 20)->nullable()->comment('颜色');
			$table->text('description')->nullable()->comment('标签描述');
			$table->json('pusher_ids')->nullable()->comment('推送人');
			$table->string('locale', 5)->nullable()->comment('标签地域');
			$table->rawIndex('(cast(`pusher_ids` as unsigned array))', 'pusher_ids_index');
			$table->timestamps();
			$table->comment('标签表');
		});

		Schema::create('tagging_tagged', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('taggable_id')->unsigned()->index();
			$table->string('taggable_type', 125)->index();
			$table->string('tag_name', 125);
			$table->string('tag_slug', 125)->index();
			$table->string('locale', 5)->nullable()->comment('地域');
			$table->comment('标签关联表');
		});

		Schema::create('tagging_tag_groups', function (Blueprint $table) {
			$table->increments('id');
			$table->string('slug', 125)->index();
			$table->string('name', 125);
			$table->comment('标签分组表');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::drop('tagging_tags');
		Schema::drop('tagging_tagged');
		Schema::drop('tagging_tag_groups');
	}
};
