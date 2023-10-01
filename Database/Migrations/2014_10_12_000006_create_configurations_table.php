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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('module')->index()->comment('模块');
            $table->string('group')->index()->comment('分组');
            $table->string('group_display_name')->nullable()->comment('分组显示名称');
            $table->string('name')->index()->comment('名称');
			$table->string('lang')->index()->default('zh_CN')->comment('语言');
            $table->string('display_name')->nullable()->comment('显示名称');
            $table->json('value')->nullable()->comment('值');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
