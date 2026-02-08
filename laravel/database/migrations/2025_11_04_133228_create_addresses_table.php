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
        Schema::create(table: 'addresses', callback: function (Blueprint $table): void {
            $table->id();
            $table->foreignId(column: 'user_id')
                ->constrained()
                ->onDelete(action: 'cascade');

            $table->string(column: 'name');
            $table->string(column: 'street');
            $table->string(column: 'city');
            $table->string(column: 'state')->nullable();
            $table->string(column: 'postal_code');
            $table->string(column: 'country');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(table: 'addresses');
    }
};
