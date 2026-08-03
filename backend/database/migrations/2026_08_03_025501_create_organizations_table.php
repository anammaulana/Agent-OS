<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void { 
            $table->id(); $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('logo_path')->nullable();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->string('locale', 10)->default('id'); 
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by') ->constrained('users') ->restrictOnDelete();
            $table->timestamps(); $table->softDeletes(); $table->index('status'); $table->index('created_by'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
