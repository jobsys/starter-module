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
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('creator_id')->index()->comment('创建者ID');
            $table->integer('principal_id')->default(0)->index()->comment('负责人ID');
            $table->string('name')->index()->comment('部门名称');
            $table->text('description')->nullable()->comment('部门描述');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->integer('sort_order')->default(0)->comment('排序，数字越大越靠前');
            $table->nestedSet();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('department_user', function (Blueprint $table) {
            $table->integer('department_id')->index()->comment('部门ID');
            $table->integer('user_id')->index()->comment('用户ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
        Schema::dropIfExists('department_user');
    }
};
