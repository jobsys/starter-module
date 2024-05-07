<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        // 字典列表
        Schema::create('dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('显示名称');
            $table->boolean('is_cascaded')->default(false)->comment('是否级联');
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
            $table->string('value')->comment('值');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->integer('sort_order')->default(0)->comment('排序:数字越大越靠前');
            $table->nestedSet();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionaries');
        Schema::dropIfExists('dictionary_items');
    }
};
