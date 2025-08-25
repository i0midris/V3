<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDiscountPercentPrecisionInPurchaseLinesTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            // Use DB::statement because Laravel doesn't support modifying decimal precision directly
            DB::statement("ALTER TABLE purchase_lines MODIFY discount_percent DECIMAL(8,6) NOT NULL DEFAULT 0.000000 COMMENT 'Inline discount percentage'");
        });
    }

    public function down()
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            DB::statement("ALTER TABLE purchase_lines MODIFY discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Inline discount percentage'");
        });
    }
}
