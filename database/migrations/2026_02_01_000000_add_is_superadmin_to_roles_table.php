<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        if (!Schema::hasColumn('roles', 'is_superadmin')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_superadmin')->default(false)->after('description');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        if (Schema::hasColumn('roles', 'is_superadmin')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('is_superadmin');
            });
        }
    }
};
