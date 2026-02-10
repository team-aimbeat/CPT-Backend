<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_videos', function (Blueprint $table) {
            $table->unsignedBigInteger('bodypart_id')->nullable()->after('equipment_id');
            $table->index('bodypart_id');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_videos', function (Blueprint $table) {
            $table->dropIndex(['bodypart_id']);
            $table->dropColumn('bodypart_id');
        });
    }
};
