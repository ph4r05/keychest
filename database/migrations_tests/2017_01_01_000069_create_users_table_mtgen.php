<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 191);
			$table->string('email', 191)->unique();
			$table->string('password', 191)->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->timestamps();
			$table->dateTime('last_email_report_sent_at')->nullable();
			$table->string('timezone', 191)->nullable();
			$table->integer('utc_offset')->default(0);
			$table->smallInteger('is_superadmin')->default(0);
			$table->dateTime('last_email_no_servers_sent_at')->nullable();
			$table->smallInteger('weekly_emails_disabled')->default(0);
			$table->dateTime('last_email_report_enqueued_at')->nullable();
			$table->dateTime('cur_login_at')->nullable();
			$table->dateTime('last_action_at')->nullable();
			$table->dateTime('last_login_at')->nullable();
			$table->integer('last_login_id')->unsigned()->nullable()->index('ix_users_last_login_id');
			$table->string('accredit', 100)->nullable();
			$table->string('accredit_own', 100)->nullable();
			$table->dateTime('closed_at')->nullable();
			$table->softDeletes();
			$table->string('email_verify_token', 24)->nullable();
			$table->dateTime('email_verified_at')->nullable();
			$table->string('notification_email', 191)->nullable();
			$table->string('weekly_unsubscribe_token', 24)->nullable();
			$table->smallInteger('cert_notif_state')->default(0);
			$table->string('cert_notif_unsubscribe_token', 24)->nullable();
			$table->bigInteger('cert_notif_last_cert_id')->nullable();
			$table->dateTime('last_email_cert_notif_sent_at')->nullable();
			$table->dateTime('last_email_cert_notif_enqueued_at')->nullable();
			$table->dateTime('auto_created_at')->nullable();
			$table->dateTime('verified_at')->nullable();
			$table->dateTime('blocked_at')->nullable();
			$table->smallInteger('new_api_keys_state')->default(0);
			$table->bigInteger('primary_owner_id')->nullable()->index('ix_users_primary_owner_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
