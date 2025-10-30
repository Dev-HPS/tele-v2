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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('residency', '50')->nullable();
            $table->string('residency_name', '50')->nullable();
            $table->string('city', '50')->nullable();
            $table->string('city_name', '50')->nullable();
            $table->string('district', '50')->nullable();
            $table->string('district_name', '50')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['residency']);
            $table->dropColumn(['residency_name']);
            $table->dropColumn(['city']);
            $table->dropColumn(['city_name']);
            $table->dropColumn(['district']);
            $table->dropColumn(['district_name']);
        });
    }
};
