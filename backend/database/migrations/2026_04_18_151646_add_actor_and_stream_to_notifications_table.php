<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->default('stream_live')->after('user_id');
            $table->foreignId('actor_user_id')->nullable()->after('type')->constrained('users')->nullOnDelete();
            $table->foreignId('stream_id')->nullable()->after('actor_user_id')->constrained('streams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stream_id');
            $table->dropConstrainedForeignId('actor_user_id');
            $table->dropColumn('type');
        });
    }
};
