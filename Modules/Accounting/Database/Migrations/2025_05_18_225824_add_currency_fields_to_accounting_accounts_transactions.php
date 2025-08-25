<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('accounting_accounts_transactions', function (Blueprint $table) {
            $table->string('currency_code', 10)->nullable()->after('amount');
            $table->decimal('exchange_rate', 22, 6)->nullable()->after('currency_code');
            $table->decimal('base_amount', 22, 4)->nullable()->after('exchange_rate');
        });
    }

    public function down()
    {
        Schema::table('accounting_accounts_transactions', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate', 'base_amount']);
        });
    }
};
