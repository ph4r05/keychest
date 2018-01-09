<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanWebAppTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_web_app', function(Blueprint $table)
		{
			$table->foreign('host_id', 'fk_scan_web_app_scan_tls_desc_id')->references('id')->on('scan_tls_desc')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_web_app', function(Blueprint $table)
		{
			$table->dropForeign('fk_scan_web_app_scan_tls_desc_id');
		});
	}

}
