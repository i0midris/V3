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
        Schema::table('accounting_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('accounting_accounts', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_accounts', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
