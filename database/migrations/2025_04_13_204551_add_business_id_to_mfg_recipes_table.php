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
        Schema::table('mfg_recipes', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->after('id')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mfg_recipes', function (Blueprint $table) {
            $table->dropColumn('business_id');
        });
    }
};
