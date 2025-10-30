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
        Schema::create('bypass_outlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('outlet_code', 50);
            $table->date('date');
            $table->integer('status')->default(0); // 0: pending, 1: approved, 2: rejected
            $table->text('description');
            $table->text('reason')->nullable(); // for rejection reason
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['outlet_code', 'date']);
            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bypass_outlets');
    }
};
