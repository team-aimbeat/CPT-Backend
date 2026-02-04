<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('offer_enabled')->default(false)->after('status');
            $table->string('offer_type')->nullable()->after('offer_enabled');
            $table->integer('offer_access_days')->nullable()->after('offer_type');
            $table->integer('offer_max_redemptions')->nullable()->after('offer_access_days');
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'offer_enabled',
                'offer_type',
                'offer_access_days',
                'offer_max_redemptions',
            ]);
        });
    }
};
