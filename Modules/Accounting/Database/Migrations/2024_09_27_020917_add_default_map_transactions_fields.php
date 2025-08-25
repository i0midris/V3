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
        if (! Schema::hasColumn('business_locations', 'accounting_default_map')) {
            Schema::table('business_locations', function (Blueprint $table): void {
                $table->text('accounting_default_map')->nullable()->after('custom_field4')
                    ->comment('Default transactions mapping of accounting module');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_locations', function (Blueprint $table): void {});
    }
};
