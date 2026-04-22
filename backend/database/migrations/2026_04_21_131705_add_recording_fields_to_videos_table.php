<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('egress_id')->nullable()->after('stream_id');
            $table->string('recording_status')->nullable()->after('egress_id');
            $table->unsignedBigInteger('size_bytes')->nullable()->after('duration');
            $table->timestamp('recorded_at')->nullable()->after('size_bytes');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn([
                'egress_id',
                'recording_status',
                'size_bytes',
                'recorded_at',
            ]);
        });
    }
};
