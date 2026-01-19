<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nrm_user_role', function (Blueprint $table) {
            $table->id();
            $table->tenantId();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('nrm_roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'role_id']);
            $table->index(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nrm_user_role');
    }
};
