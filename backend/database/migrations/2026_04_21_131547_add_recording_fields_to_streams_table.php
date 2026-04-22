<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            $table->string('recording_egress_id')->nullable()->after('room_name');
            $table->string('recording_status')->nullable()->after('recording_egress_id');
            $table->timestamp('recording_started_at')->nullable()->after('recording_status');
            $table->timestamp('recording_ended_at')->nullable()->after('recording_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            $table->dropColumn([
                'recording_egress_id',
                'recording_status',
                'recording_started_at',
                'recording_ended_at',
            ]);
        });
    }
};
