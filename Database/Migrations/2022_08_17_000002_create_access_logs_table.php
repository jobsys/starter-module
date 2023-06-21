<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0)->index()->comment('谁');
            $table->string('user_type')->default('user')->index()->comment('用户类型');
            $table->string('ip')->nullable()->comment('IP 地址');
            $table->string('brief')->nullable()->comment('摘要');
            $table->text('object')->nullable()->comment('操作对象');
            $table->string('action')->index()->comment('操作名称');
            $table->string('effect')->index()->nullable()->comment('操作结果');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_logs');
    }
};
