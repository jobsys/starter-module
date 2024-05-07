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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->integer('homology_id')->default(0)->comment('同源ID');
            $table->string('lang')->default('zh_CN')->nullable()->index()->comment('语言');
            $table->string('module')->index()->comment('所属模块');
            $table->string('group')->index()->nullable()->comment('分组');
            $table->string('name')->index()->comment('名称');
            $table->string('slug')->index()->nullable()->comment('别名');
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
        Schema::dropIfExists('categories');
    }
};
