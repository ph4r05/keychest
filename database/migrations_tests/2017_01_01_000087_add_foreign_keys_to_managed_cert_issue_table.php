<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedCertIssueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_cert_issue', function(Blueprint $table)
		{
			$table->foreign('certificate_id', 'fk_managed_cert_issue_certificate_id')->references('id')->on('certificates')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('new_certificate_id', 'fk_managed_cert_issue_new_certificate_id')->references('id')->on('certificates')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('solution_id', 'fk_managed_cert_issue_managed_solution_id')->references('id')->on('managed_solutions')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('service_id', 'fk_managed_cert_issue_service_id')->references('id')->on('managed_services')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_cert_issue', function(Blueprint $table)
		{
			$table->dropForeign('fk_managed_cert_issue_certificate_id');
			$table->dropForeign('fk_managed_cert_issue_new_certificate_id');
			$table->dropForeign('fk_managed_cert_issue_managed_solution_id');
			$table->dropForeign('fk_managed_cert_issue_service_id');
		});
	}

}
