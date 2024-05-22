<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTaxMethodNullaleToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('tax_method',10)->nullable()->change();
        });
		
		Schema::table('services', function (Blueprint $table) {
            $table->string('tax_method',10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('tax_method',10)->change();
        });
		
		Schema::table('services', function (Blueprint $table) {
            $table->string('tax_method',10)->change();
        });
    }
}
