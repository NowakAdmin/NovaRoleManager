<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nrm_permissions', function (Blueprint $table) {
            $table->id();
            $table->tenantId();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('resource')->index();
            $table->string('action')->index();
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
            $table->index(['resource', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nrm_permissions');
    }
};
