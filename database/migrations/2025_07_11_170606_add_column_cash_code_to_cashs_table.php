<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCashCodeToCashsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashs', function (Blueprint $table) {
            $table->string('cash_code', 4)->unique()->nullable()->after('cash_id')->comment('Codigo punto de venta para el DTE');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('codigo_sucursal', 4)->unique()->nullable()->comment('Codigo establecimiento para el DTE')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashs', function (Blueprint $table) {
            $table->dropColumn('cash_code');
        });
    }
}
