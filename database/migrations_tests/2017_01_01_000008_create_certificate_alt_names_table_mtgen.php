<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCertificateAltNamesTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('certificate_alt_names', function(Blueprint $table)
		{
			$table->bigInteger('cert_id')->index('ix_certificate_alt_names_cert_id');
			$table->string('alt_name')->index('ix_certificate_alt_names_alt_name');
			$table->smallInteger('is_wildcard')->default(0);
			$table->primary(['cert_id','alt_name','is_wildcard']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('certificate_alt_names');
	}

}
