<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedTestProfilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_test_profiles', function(Blueprint $table)
		{
			$table->foreign('scan_service_id', 'fk_managed_test_profiles_scan_service_id')->references('id')->on('watch_service')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('top_domain_id', 'fk_managed_test_profiles_base_domain_id')->references('id')->on('base_domain')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_test_profiles', function(Blueprint $table)
		{
			$table->dropForeign('fk_managed_test_profiles_scan_service_id');
			$table->dropForeign('fk_managed_test_profiles_base_domain_id');
		});
	}

}
