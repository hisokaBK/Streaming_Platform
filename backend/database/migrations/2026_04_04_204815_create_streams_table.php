<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('status', ['live', 'ended'])->default('live');
            $table->string('stream_key')->unique();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->unsignedInteger('current_viewers')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
