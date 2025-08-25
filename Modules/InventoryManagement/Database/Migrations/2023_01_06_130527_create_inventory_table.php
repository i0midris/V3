<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('business_locations');
            $table->text('name')->nullable(false);
            $table->timestamp('end_date')->nullable();
            $table->boolean('status')->default(false);
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
        Schema::dropIfExists('inventory');
    }
}
