<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToWatchServiceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watch_service', function(Blueprint $table)
		{
			$table->foreign('crtsh_input_id', 'fk_watch_service_crtsh_input_id')->references('id')->on('crtsh_input')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('top_domain_id', 'watch_service_base_domain_id')->references('id')->on('base_domain')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('watch_service', function(Blueprint $table)
		{
			$table->dropForeign('fk_watch_service_crtsh_input_id');
			$table->dropForeign('watch_service_base_domain_id');
		});
	}

}
