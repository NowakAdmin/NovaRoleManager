<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Multitenancy\Models\Tenant;

return new class extends Migration {
    public function up(): void
    {
        // Spatie permission tables are published separately
        // This migration ensures they exist and are tenant-aware

        // Roles table
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Tenant::class)->nullable()->constrained()->cascadeOnDelete();
                $table->string('name')->unique();
                $table->string('guard_name')->default('web');
                $table->string('description')->nullable();
                $table->timestamps();

                $table->unique(['name', 'guard_name', 'tenant_id']);
            });
        }

        // Permissions table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Tenant::class)->nullable()->constrained()->cascadeOnDelete();
                $table->string('name')->unique();
                $table->string('guard_name')->default('web');
                $table->string('description')->nullable();
                $table->string('resource')->nullable();
                $table->string('action')->nullable();
                $table->timestamps();

                $table->unique(['name', 'guard_name', 'tenant_id']);
            });
        }

        // Role-Permission pivot table
        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');

                $table->primary(['permission_id', 'role_id']);
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            });
        }

        // Model-Role pivot table
        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->morphs('model', column_names: ['model_type', 'model_id']);

                $table->primary(['role_id', 'model_id', 'model_type']);
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->index(['model_id', 'model_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
