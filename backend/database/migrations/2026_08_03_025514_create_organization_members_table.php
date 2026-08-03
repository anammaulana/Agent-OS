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
       Schema::create('organization_members', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('organization_id') ->constrained('organizations') ->cascadeOnDelete(); 
        $table->foreignId('user_id') ->constrained('users') ->cascadeOnDelete(); 
        $table->string('role', 20)->default('member'); 
        $table->string('status', 20)->default('active'); 
        $table->timestamp('joined_at')->nullable(); 
        $table->foreignId('invited_by') ->nullable() ->constrained('users') ->nullOnDelete(); 
        $table->timestamps(); 
        $table->unique( ['organization_id', 'user_id'], 'organization_member_unique' ); 
        $table->index(['organization_id', 'role']); 
        $table->index(['user_id', 'status']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_members');
    }
};
