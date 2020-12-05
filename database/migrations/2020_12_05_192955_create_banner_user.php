<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannerUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id');
            $table->foreign('banner_id')->references('id')->on('banners')
                ->cascadeOnDelete();
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')
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
        Schema::dropIfExists('banner_user');
    }
}
