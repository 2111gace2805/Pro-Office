<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderItemTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_item_taxes', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->bigInteger('purchase_order_id');
            $table->bigInteger('purchase_order_item_id');
            $table->bigInteger('tax_id');
            $table->string('name',100);
            $table->decimal('amount',10,2);
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
        Schema::dropIfExists('purchase_order_item_taxes');
    }
}
