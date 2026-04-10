<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_category', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('video_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['video_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_category');
    }
};
