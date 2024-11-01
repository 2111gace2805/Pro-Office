<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Change2ReferenceMunidepaIdInContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->bigInteger('munidepa_id', unsigned: true)->comment('hace referencia al campo dist_id de la tabla distritos, anteriormente referenciaba a municipios')->change();
            $table->foreign('munidepa_id')->references('dist_id')->on('districts')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            // $table->bigInteger('dist_id', unsigned: true);
            // $table->foreign('dist_id', 'contacts_dist_id_foreign')->references('dist_id')->on('districts')->cascadeOnUpdate();

            $table->integer('munidepa_id', unsigned: false)->change();
            $table->foreign('munidepa_id', 'contacts_ibfk_5')->references('munidepa_id')->on('municipios');
        });
    }
}
