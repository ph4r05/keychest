<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedServicesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_services', function(Blueprint $table)
		{
			$table->foreign('agent_id', 'managed_services_agent_id')->references('id')->on('keychest_agent')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('owner_id', 'managed_services_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('svc_watch_id', 'managed_services_svc_watch_id')->references('id')->on('watch_target')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('test_profile_id', 'managed_services_test_profile_id')->references('id')->on('managed_test_profiles')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_services', function(Blueprint $table)
		{
			$table->dropForeign('managed_services_agent_id');
			$table->dropForeign('managed_services_owner_id');
			$table->dropForeign('managed_services_svc_watch_id');
			$table->dropForeign('managed_services_test_profile_id');
		});
	}

}
