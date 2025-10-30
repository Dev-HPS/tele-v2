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
        Schema::create('non_ordering_outlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('outlet_code', 50);
            $table->text('description');
            $table->uuid('category_id');
            $table->uuid('created_by');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            // Foreign key constraint
            $table->foreign('category_id')->references('id')->on('non_ordering_categories');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('non_ordering_outlets');
    }
};
