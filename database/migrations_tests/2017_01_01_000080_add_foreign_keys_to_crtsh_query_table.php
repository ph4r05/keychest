<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCrtshQueryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('crtsh_query', function(Blueprint $table)
		{
			$table->foreign('input_id', 'crtsh_watch_input_id')->references('id')->on('crtsh_input')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('sub_watch_id', 'crtsh_watch_sub_target_id')->references('id')->on('subdomain_watch_target')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('watch_id', 'crtsh_watch_target_id')->references('id')->on('watch_target')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('service_id', 'crtsh_watch_watch_service_id')->references('id')->on('watch_service')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('crtsh_query', function(Blueprint $table)
		{
			$table->dropForeign('crtsh_watch_input_id');
			$table->dropForeign('crtsh_watch_sub_target_id');
			$table->dropForeign('crtsh_watch_target_id');
			$table->dropForeign('crtsh_watch_watch_service_id');
		});
	}

}
