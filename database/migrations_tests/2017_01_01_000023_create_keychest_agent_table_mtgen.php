<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateKeychestAgentTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('keychest_agent', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name', 191);
			$table->string('api_key', 191)->index('ix_keychest_agent_api_key');
			$table->bigInteger('organization_id')->index('ix_keychest_agent_organization_id');
			$table->timestamps();
			$table->dateTime('last_seen_active_at')->nullable();
			$table->string('last_seen_ip', 191)->nullable();
			$table->bigInteger('owner_id')->index('ix_keychest_agent_owner_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('keychest_agent');
	}

}
