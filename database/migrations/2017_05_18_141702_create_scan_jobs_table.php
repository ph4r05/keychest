<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScanJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scan_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            $table->string('scan_host');
            $table->string('scan_scheme')->nullable();
            $table->string('scan_port')->nullable();

            $table->timestamps();
            $table->string('state')->nullable();
            $table->string('progress')->nullable();

            $table->integer('user_id')->unsigned()->nullable();
            $table->string('user_ip')->nullable();
            $table->string('user_sess')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scan_jobs');
    }
}
