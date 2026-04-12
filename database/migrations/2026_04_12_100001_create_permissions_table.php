<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profile × module rights (CRUD flags). Table name kept as "permissions" per domain naming.
     * Columns use can_* to avoid SQL reserved words (e.g. CREATE).
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_read')->default(false);
            $table->timestamps();

            $table->unique(['profile_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
