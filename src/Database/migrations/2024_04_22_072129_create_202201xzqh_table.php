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
        /**
         * 来源
         *
         * 2022年中华人民共和国县以上行政区划代码
         */
        Schema::create('202201xzqh', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->unique()->comment('行政区划代码');
            $table->string('title')->comment('名称');
            $table->tinyInteger('type')->comment('0省级 1市级 2区县');
            $table->integer('parent_id')->default(0)->comment('父级ID');
            $table->integer('superior_id')->default(0)->comment('爷级ID');
            $table->index(['code']);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('202201xzqh');
    }
};
