<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanTlsDescExtTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_tls_desc_ext', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('tls_desc_id')->index('ix_scan_tls_desc_ext_tls_desc_id');
			$table->bigInteger('tls_params_id')->index('ix_scan_tls_desc_ext_tls_params_id');
			$table->unique(['tls_desc_id','tls_params_id'], 'uk_scan_tls_desc_ext_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_tls_desc_ext');
	}

}
