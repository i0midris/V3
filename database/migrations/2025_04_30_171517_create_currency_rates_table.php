<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyRatesTable extends Migration
{
    public function up()
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->increments('id'); // INT UNSIGNED
            $table->unsignedInteger('business_id'); // Must match business.id type
            $table->string('currency_name');
            $table->string('currency_code', 10);
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        
            $table->foreign('business_id')
                  ->references('id')
                  ->on('business')
                  ->onDelete('cascade');
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('currency_rates');
    }
}
