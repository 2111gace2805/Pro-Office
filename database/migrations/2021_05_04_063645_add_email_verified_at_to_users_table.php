<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailVerifiedAtToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('role_id')->nullable()->after('user_type');
            $table->timestamp('email_verified_at')->nullable()->after('profile_picture');
			$table->string('provider')->nullable()->after('email_verified_at');
            $table->string('provider_id')->nullable()->after('provider');
        });
		
		Schema::table('permissions', function (Blueprint $table) {
			$table->dropColumn('user_id');
            $table->bigInteger('role_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['role_id']);
			$table->bigInteger('user_id')->after('id');
        });
		
		Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role_id', 'email_verified_at', 'provider', 'provider_id']);
        });
    }
}
