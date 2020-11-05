<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategorySpeedrun extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_speedrun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->foreign('category_id')->references('id')->on('categories')
                ->cascadeOnDelete();
            $table->foreignId('speedrun_id');
            $table->foreign('speedrun_id')->references('id')->on('speedruns')
                ->cascadeOnDelete();
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
        Schema::dropIfExists('category_speedrun');
    }
}
