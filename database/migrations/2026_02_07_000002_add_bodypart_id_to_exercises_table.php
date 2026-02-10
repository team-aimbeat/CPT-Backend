<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->unsignedBigInteger('bodypart_id')->nullable()->after('bodypart_ids');
        });

        // Backfill from bodypart_ids (first item) if present.
        DB::statement("
            UPDATE exercises
            SET bodypart_id = JSON_UNQUOTE(JSON_EXTRACT(bodypart_ids, '$[0]'))
            WHERE bodypart_id IS NULL AND bodypart_ids IS NOT NULL AND bodypart_ids <> ''
        ");
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn('bodypart_id');
        });
    }
};
