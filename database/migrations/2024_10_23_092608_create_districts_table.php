<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id('dist_id');
            $table->integer('munidepa_id', unsigned: false)->nullable()->comment('id del municipio');
            $table->string('dist_name');
            $table->enum('dist_status', ['Active', 'Inactive'])->default('Active');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('munidepa_id')->references('munidepa_id')->on('municipios')->cascadeOnUpdate()->cascadeOnDelete();
        });
        DB::statement("ALTER TABLE districts COMMENT = 'ALIAS: dist'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('districts');
    }
}
