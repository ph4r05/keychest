<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateKeycheckerStatsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('keychecker_stats', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('stat_id', 64)->nullable()->unique('keychecker_stats_stat_id');
			$table->integer('number')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('keychecker_stats');
	}

}
