<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOwnerWatchTargetTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('owner_watch_target', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('owner_id')->index('ix_owner_watch_target_owner_id');
			$table->bigInteger('watch_id')->index('ix_owner_watch_target_watch_id');
			$table->timestamps();
			$table->softDeletes();
			$table->dateTime('disabled_at')->nullable();
			$table->dateTime('auto_scan_added_at')->nullable();
			$table->bigInteger('scan_periodicity')->nullable();
			$table->integer('scan_type')->nullable();
			$table->unique(['owner_id','watch_id'], 'uk_owner_watch_target_owner_watch');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('owner_watch_target');
	}

}
