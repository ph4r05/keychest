<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedCertIssueTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_cert_issue', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('solution_id')->index('ix_managed_cert_issue_solution_id');
			$table->bigInteger('service_id')->index('ix_managed_cert_issue_service_id');
			$table->bigInteger('certificate_id')->nullable()->index('ix_managed_cert_issue_certificate_id');
			$table->bigInteger('new_certificate_id')->nullable()->index('ix_managed_cert_issue_new_certificate_id');
			$table->text('affected_certs_ids', 65535)->nullable();
			$table->text('request_data', 65535)->nullable();
			$table->dateTime('last_issue_at')->nullable();
			$table->smallInteger('last_issue_status')->nullable();
			$table->text('last_issue_data', 65535)->nullable();
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
		Schema::drop('managed_cert_issue');
	}

}
