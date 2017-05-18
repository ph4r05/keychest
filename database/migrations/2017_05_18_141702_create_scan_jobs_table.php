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
            $table->uuid('id');
            $table->primary('id');
            $table->string('scan_host');
            $table->string('scan_scheme')->nullable();
            $table->string('scan_port')->nullable();
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
        Schema::dropIfExists('scan_jobs');
    }
}
