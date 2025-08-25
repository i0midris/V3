<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('superadmin_coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('coupon_code');
            $table->string('discount_type');
            $table->decimal('discount');
            $table->date('expiry_date')->nullable();
            $table->string('applied_on_packages')->nullable();
            $table->boolean('is_active');
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
        Schema::dropIfExists('coupons');
    }
};
