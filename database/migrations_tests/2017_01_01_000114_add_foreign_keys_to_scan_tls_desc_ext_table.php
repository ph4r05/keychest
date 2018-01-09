<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanTlsDescExtTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_tls_desc_ext', function(Blueprint $table)
		{
			$table->foreign('tls_desc_id', 'fk_scan_tls_desc_ext_scan_tls_desc_id')->references('id')->on('scan_tls_desc')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('tls_params_id', 'fk_scan_tls_desc_ext_scan_tls_params_id')->references('id')->on('scan_tls_params')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_tls_desc_ext', function(Blueprint $table)
		{
			$table->dropForeign('fk_scan_tls_desc_ext_scan_tls_desc_id');
			$table->dropForeign('fk_scan_tls_desc_ext_scan_tls_params_id');
		});
	}

}
