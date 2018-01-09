<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToKeychestAgentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('keychest_agent', function(Blueprint $table)
		{
			$table->foreign('owner_id', 'fk_keychest_agent_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('organization_id', 'keychest_agent_organization_id')->references('id')->on('organization')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('keychest_agent', function(Blueprint $table)
		{
			$table->dropForeign('fk_keychest_agent_owner_id');
			$table->dropForeign('keychest_agent_organization_id');
		});
	}

}
