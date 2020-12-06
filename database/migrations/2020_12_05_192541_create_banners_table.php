<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->cascadeOnDelete();
            $table->string('title', 256);
            $table->string('bcolor', 32)->default('white');
            $table->string('tcolor', 32)->default('black');
            $table->string('url', 128)->nullable();
            $table->dateTime('expiration')->default(now()->addDays(7)->toDateTimeString());
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
        Schema::dropIfExists('banners');
    }
}
