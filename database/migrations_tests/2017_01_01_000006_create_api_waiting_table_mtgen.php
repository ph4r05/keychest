<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateApiWaitingTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_waiting', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->timestamps();
			$table->bigInteger('api_key_id')->nullable()->index('ix_api_waiting_api_key_id');
			$table->string('waiting_id', 36)->index('ix_api_waiting_waiting_id');
			$table->string('object_operation', 42)->nullable()->index('ix_api_waiting_object_operation');
			$table->string('object_type', 42)->nullable()->index('ix_api_waiting_object_type');
			$table->string('object_key', 191)->nullable()->index('ix_api_waiting_object_key');
			$table->text('object_value', 65535)->nullable();
			$table->bigInteger('certificate_id')->nullable()->index('ix_api_waiting_certificate_id');
			$table->text('computed_data', 65535)->nullable();
			$table->dateTime('last_scan_at')->nullable();
			$table->smallInteger('last_scan_status')->nullable();
			$table->text('last_scan_data', 65535)->nullable();
			$table->dateTime('ct_found_at')->nullable();
			$table->dateTime('processed_at')->nullable();
			$table->dateTime('finished_at')->nullable();
			$table->smallInteger('approval_status')->default(0);
			$table->string('client_req_id', 128)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('api_waiting');
	}

}
