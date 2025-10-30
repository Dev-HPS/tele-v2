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
        Schema::create('outlet_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('outlet_call_id')->nullable(); // UUID outlet_call yang di-log
            $table->string('user_id'); // UUID user yang melakukan aksi
            $table->enum('action', ['create', 'update', 'delete', 'approve', 'reject', 'restore']); // Jenis aksi
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Status log
            $table->text('description')->nullable(); // Deskripsi/alasan
            $table->json('old_data')->nullable(); // Data sebelum perubahan
            $table->json('new_data')->nullable(); // Data setelah perubahan
            $table->string('ip_address', 45)->nullable(); // IP address user
            $table->text('user_agent')->nullable(); // Browser/device info
            $table->timestamps();

            // Indexes for better performance
            $table->index(['outlet_call_id', 'action']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outlet_call_logs');
    }
};
