<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanTlsParamsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_tls_params', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('tls_ver', 16)->nullable();
			$table->smallInteger('key_type')->nullable()->default(0);
			$table->bigInteger('cipersuite_set')->nullable()->default(0);
			$table->unique(['tls_ver','key_type','cipersuite_set'], 'uk_scan_tls_params_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_tls_params');
	}

}
