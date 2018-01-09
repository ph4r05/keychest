<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOwnerSubdomainWatchTargetTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('owner_subdomain_watch_target', function(Blueprint $table)
		{
			$table->foreign('owner_id', 'fk_owner_subdomain_watch_target_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('watch_id', 'fk_owner_subdomain_watch_target_watch_id')->references('id')->on('subdomain_watch_target')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('owner_subdomain_watch_target', function(Blueprint $table)
		{
			$table->dropForeign('fk_owner_subdomain_watch_target_owner_id');
			$table->dropForeign('fk_owner_subdomain_watch_target_watch_id');
		});
	}

}
