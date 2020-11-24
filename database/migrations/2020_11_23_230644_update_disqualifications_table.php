<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDisqualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('disqualifications', function (Blueprint $table)
        {
            $table->foreignId('speedrun_id')->unique()->change();
            $table->string('evidence')->nullable()->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disqualifications', function (Blueprint $table) {
            $table->dropColumn('evidence');
            $table->foreignId('speedrun_id')->change();
        });
    }
}
