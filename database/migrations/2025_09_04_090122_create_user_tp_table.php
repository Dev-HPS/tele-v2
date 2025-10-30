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
        Schema::create('user_tp', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // UUID string
            $table->string('tp', 10);
            $table->timestamps();

            // Index for better performance
            $table->index(['user_id', 'tp']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_tp');
    }
};
