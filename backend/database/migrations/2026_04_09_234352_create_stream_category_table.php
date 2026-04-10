<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_category', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('stream_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['stream_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_category');
    }
};
