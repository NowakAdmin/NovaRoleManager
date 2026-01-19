<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nrm_role_permission', function (Blueprint $table) {
            $table->id();
            $table->tenantId();
            $table->foreignId('role_id')->constrained('nrm_roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('nrm_permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'role_id', 'permission_id']);
            $table->index(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nrm_role_permission');
    }
};
