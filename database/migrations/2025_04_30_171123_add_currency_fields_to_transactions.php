<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyFieldsToTransactions extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('currency_code', 10)->nullable()->after('exchange_rate');
            $table->decimal('base_final_total', 22, 4)->nullable()->after('final_total');

            // Optional: add an index if you will query by currency_code
            $table->index('currency_code');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'base_final_total']);
        });
    }
}
