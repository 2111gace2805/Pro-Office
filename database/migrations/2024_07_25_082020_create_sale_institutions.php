<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleInstitutions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code_isss', 25)->nullable();
            $table->string('code_minsal', 25)->nullable();
            $table->string('code_onu', 25)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_institutions');
    }
}
