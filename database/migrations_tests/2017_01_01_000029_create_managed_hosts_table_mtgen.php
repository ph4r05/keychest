<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedHostsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_hosts', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('host_name')->nullable();
			$table->string('host_addr')->nullable();
			$table->integer('ssh_port')->nullable();
			$table->text('host_desc', 65535)->nullable();
			$table->text('host_data', 65535)->nullable();
			$table->bigInteger('agent_id')->nullable()->index('ix_managed_hosts_agent_id');
			$table->timestamps();
			$table->softDeletes();
			$table->bigInteger('ssh_key_id')->nullable()->index('ix_managed_hosts_ssh_key_id');
			$table->bigInteger('owner_id')->nullable()->index('ix_managed_hosts_owner_id');
			$table->string('host_os')->nullable();
			$table->string('host_os_ver')->nullable();
			$table->string('host_secret')->nullable();
			$table->unique(['host_addr','ssh_port','owner_id','agent_id'], 'uk_managed_hosts_host_uk');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('managed_hosts');
	}

}
