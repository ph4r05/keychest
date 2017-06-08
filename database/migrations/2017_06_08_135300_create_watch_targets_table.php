<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWatchTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watch_target', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('scan_host');
            $table->string('scan_scheme')->nullable();
            $table->unsignedInteger('scan_port')->nullable();
            $table->bigInteger('scan_periodicity')->nullable();
            $table->smallInteger('scan_connect')->default(0);

            $table->timestamps();
            $table->timestamp('last_scan_at')->nullable();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('watch_target');
    }
}
