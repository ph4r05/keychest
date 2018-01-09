<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanGapsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_gaps', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('watch_id')->nullable()->index('ix_scan_gaps_watch_id');
			$table->smallInteger('scan_code');
			$table->smallInteger('scan_type')->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('gap_start')->nullable();
			$table->dateTime('gap_end')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_gaps');
	}

}
