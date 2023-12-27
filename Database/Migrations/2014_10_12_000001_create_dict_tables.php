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

        // 字典列表
        Schema::create('dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('显示名称');
            $table->string('slug')->index()->unique()->comment('名称');
            $table->string('description')->nullable()->comment('描述');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->timestamps();
        });

        // 字典项
        Schema::create('dictionary_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dictionary_id')->index()->comment('字典ID');
            $table->string('name')->nullable()->comment('显示名称');
            $table->integer('parent_id')->unsigned()->nullable()->index('父级ID');
            $table->integer('position', false, true);
            $table->string('value')->comment('值');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->integer('sort_order')->default(0)->comment('排序:数字越大越靠前');
            $table->foreign('parent_id')
                ->references('id')
                ->on('dictionary_items')
                ->onDelete('set null');
            $table->timestamps();
        });

        //字典项关系表
        Schema::create('dictionary_items_closure', function (Blueprint $table) {
            $table->increments('closure_id');

            $table->integer('ancestor')->unsigned();
            $table->integer('descendant')->unsigned();
            $table->integer('depth')->unsigned();

            $table->foreign('ancestor')
                ->references('id')
                ->on('dictionary_items')
                ->onDelete('cascade');

            $table->foreign('descendant')
                ->references('id')
                ->on('dictionary_items')
                ->onDelete('cascade');

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionaries');
        Schema::dropIfExists('dictionary_items_closure');
        Schema::dropIfExists('dictionary_items');
    }
};
