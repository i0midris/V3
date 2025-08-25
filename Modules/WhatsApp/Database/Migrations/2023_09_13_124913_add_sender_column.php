<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSenderColumn extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_gateway', function (Blueprint $table): void {
            $table->bigInteger('sender')->nullable()->after('wa_server')->comment('Add sender for MPWA')->nullable();
        });
    }
}
