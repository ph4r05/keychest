<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWatchServiceTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('watch_service', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('service_name')->unique('uk_watch_service_service_name');
			$table->bigInteger('top_domain_id')->nullable()->index('ix_watch_service_top_domain_id');
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->smallInteger('last_scan_state')->nullable();
			$table->bigInteger('crtsh_input_id')->nullable()->index('ix_watch_service_crtsh_input_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('watch_service');
	}

}
