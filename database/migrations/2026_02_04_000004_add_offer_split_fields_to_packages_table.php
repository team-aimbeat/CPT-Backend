<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('offer_same_access_count')->nullable()->after('offer_max_redemptions');
            $table->integer('offer_free_access_count')->nullable()->after('offer_same_access_count');
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'offer_same_access_count',
                'offer_free_access_count',
            ]);
        });
    }
};
