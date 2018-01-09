<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCertificateAltNamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('certificate_alt_names', function(Blueprint $table)
		{
			$table->foreign('cert_id', 'cert_alt_name_cert_id')->references('id')->on('certificates')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('certificate_alt_names', function(Blueprint $table)
		{
			$table->dropForeign('cert_alt_name_cert_id');
		});
	}

}
