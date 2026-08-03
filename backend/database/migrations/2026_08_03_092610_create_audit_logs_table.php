<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('resource_type', 150)->nullable();
            $table->string('resource_id', 100)->nullable();
            $table->string('description', 500)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['organization_id', 'created_at',]);
            $table->index(['actor_id', 'created_at',]);
            $table->index(['resource_type', 'resource_id',]);
            $table->index('action');
            $table->index('request_id');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
