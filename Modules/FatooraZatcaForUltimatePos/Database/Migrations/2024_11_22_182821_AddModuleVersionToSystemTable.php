<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddModuleVersionToSystemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $is_exist = DB::table('system')->where('key', 'fatoorazatcaforultimatepos_version')->exists();

        if (! $is_exist) {
            DB::table('system')->insert([
                'key' => 'fatoorazatcaforultimatepos_version',
                'value' => config('fatoorazatcaforultimatepos.module_version'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
