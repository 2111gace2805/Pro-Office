<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeneralDiscountToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('general_discount', 10, 2)->default(0)->comment('valor descontado de la factura en dinero');
            $table->foreignId('general_discount_id')->nullable()->references('id')->on('general_discounts')->cascadeOnUpdate();
            $table->enum('general_discount_type', ['Percentage', 'Fixed'])->nullable();
            $table->decimal('general_discount_value', 10, 2)->default(0)->comment('valor en porcentaje o fijo descontado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['general_discount_id']);
            $table->dropColumn(['general_discount', 'general_discount_id', 'general_discount_type', 'general_discount_value']);
        });
    }
}
