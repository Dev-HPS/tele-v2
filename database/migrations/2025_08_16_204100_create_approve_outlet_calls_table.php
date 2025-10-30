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
        Schema::create('approve_outlet_calls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('outlet_code', '50')->nullable();
            $table->string('outlet_name')->nullable();
            $table->string('outlet_owner')->nullable();
            $table->string('outlet_phone')->nullable();
            $table->string('outlet_address')->nullable();
            $table->string('tp', '100')->nullable();
            $table->string('tp_name')->nullable();
            $table->string('residency', '100')->nullable();
            $table->string('residency_name')->nullable();
            $table->string('city', '100')->nullable();
            $table->string('city_name')->nullable();
            $table->string('district', '100')->nullable();
            $table->string('district_name')->nullable();
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])->nullable();
            $table->enum('type', ['Tambah', 'Hapus'])->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('reason')->nullable(); // reason delete data
            $table->string('description')->nullable(); // reject reason
            $table->string('sbu_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approve_outlet_calls');
    }
};
