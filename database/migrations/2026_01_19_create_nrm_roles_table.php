<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nrm_roles', function (Blueprint $table) {
            $table->id();
            $table->tenantId();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_superadmin')->default(false)->index();
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
            $table->index('is_superadmin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nrm_roles');
    }
};
