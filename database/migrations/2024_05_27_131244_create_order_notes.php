<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_notes', function (Blueprint $table) {

            $table->id();
            $table->string('order_number', 200);
            $table->string('sales_company', 300);
            
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('contacts')->onDelete('set null');
            
            $table->string('num_public_tender', 200);
            $table->string('num_contract', 200);
            $table->date('deliver_date_contract');

            $table->integer('status')->nullable()->default(1)->comment('0 = rechazada ( se hace devolucion de items ); 1 = ingresada; 2 = completada;');
            $table->integer('invoiced')->nullable()->default(0)->comment('0 = order no facturada ; 1 = order facturada;');

            $table->text('note')->nullable();
            $table->longText('details');

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
        Schema::dropIfExists('order_notes');
    }
}
