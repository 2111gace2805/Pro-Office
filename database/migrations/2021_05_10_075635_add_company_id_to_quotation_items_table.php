<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToQuotationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->bigInteger('company_id')->after('sub_total'); 
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->integer('status')->default(0)->after('company_id'); 
            $table->bigInteger('invoice_id')->nullable()->after('status'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('invoice_id');
        });
    }
}
