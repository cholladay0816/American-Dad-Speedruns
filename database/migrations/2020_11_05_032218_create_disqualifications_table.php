<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisqualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disqualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('speedrun_id');
            $table->foreign('speedrun_id')->references('id')->on('speedruns')
                ->cascadeOnDelete();
            $table->string('reason');
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
        Schema::dropIfExists('disqualifications');
    }
}
