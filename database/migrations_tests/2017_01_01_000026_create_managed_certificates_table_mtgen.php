<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedCertificatesTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_certificates', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('solution_id')->index('ix_managed_certificates_solution_id');
			$table->bigInteger('service_id')->index('ix_managed_certificates_service_id');
			$table->string('certificate_key')->nullable();
			$table->bigInteger('certificate_id')->nullable()->index('ix_managed_certificates_certificate_id');
			$table->bigInteger('deprecated_certificate_id')->nullable()->index('ix_managed_certificates_deprecated_certificate_id');
			$table->text('cert_params', 65535)->nullable();
			$table->dateTime('record_deprecated_at')->nullable();
			$table->dateTime('last_check_at')->nullable();
			$table->smallInteger('last_check_status')->nullable();
			$table->text('last_check_data', 65535)->nullable();
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
		Schema::drop('managed_certificates');
	}

}
