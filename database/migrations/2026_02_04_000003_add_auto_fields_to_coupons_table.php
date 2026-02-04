<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_auto_generated')->default(false)->after('first_purchase_only');
            $table->unsignedBigInteger('source_subscription_id')->nullable()->after('is_auto_generated');
            $table->index('source_subscription_id');
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['source_subscription_id']);
            $table->dropColumn(['is_auto_generated', 'source_subscription_id']);
        });
    }
};
