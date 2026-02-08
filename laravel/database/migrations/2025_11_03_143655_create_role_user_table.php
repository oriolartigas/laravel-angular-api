<?php

declare(strict_types=1);

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
        Schema::create(table: 'role_user', callback: function (Blueprint $table): void {
            $table->foreignId(column: 'user_id')->constrained()->onDelete(action: 'cascade');
            $table->foreignId(column: 'role_id')->constrained()->onDelete(action: 'cascade');
            $table->primary(columns: ['user_id', 'role_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(table: 'role_user');
    }
};
