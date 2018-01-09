<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedCertificatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_certificates', function(Blueprint $table)
		{
			$table->foreign('certificate_id', 'fk_managed_certificates_certificate_id')->references('id')->on('certificates')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('deprecated_certificate_id', 'fk_managed_certificates_deprecated_certificate_id')->references('id')->on('certificates')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('service_id', 'fk_managed_certificate_service_id')->references('id')->on('managed_services')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('solution_id', 'fk_managed_certificate_managed_solution_id')->references('id')->on('managed_solutions')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_certificates', function(Blueprint $table)
		{
			$table->dropForeign('fk_managed_certificates_certificate_id');
			$table->dropForeign('fk_managed_certificates_deprecated_certificate_id');
			$table->dropForeign('fk_managed_certificate_service_id');
			$table->dropForeign('fk_managed_certificate_managed_solution_id');
		});
	}

}
