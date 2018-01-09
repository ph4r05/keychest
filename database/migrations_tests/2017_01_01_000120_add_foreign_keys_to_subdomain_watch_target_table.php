<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSubdomainWatchTargetTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('subdomain_watch_target', function(Blueprint $table)
		{
			$table->foreign('top_domain_id', 'sub_wt_base_domain_id')->references('id')->on('base_domain')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('subdomain_watch_target', function(Blueprint $table)
		{
			$table->dropForeign('sub_wt_base_domain_id');
		});
	}

}
