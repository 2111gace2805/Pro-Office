<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('name',50);
			$table->text('description')->nullable();
			$table->bigInteger('company_id');
            $table->timestamps();
        });
	
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_roles');
    }
	
}
