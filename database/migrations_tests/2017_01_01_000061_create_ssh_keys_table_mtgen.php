<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSshKeysTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ssh_keys', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('key_id', 64)->nullable()->unique('ssh_keys_key_id');
			$table->text('pub_key', 65535)->nullable();
			$table->text('priv_key', 65535)->nullable();
			$table->integer('bit_size')->nullable();
			$table->smallInteger('key_type')->nullable();
			$table->smallInteger('storage_type')->nullable();
			$table->integer('user_id')->unsigned()->nullable()->index('ix_ssh_keys_user_id');
			$table->integer('rec_version')->nullable();
			$table->timestamps();
			$table->dateTime('revoked_at')->nullable();
			$table->bigInteger('owner_id')->nullable()->index('ix_ssh_keys_owner_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ssh_keys');
	}

}
