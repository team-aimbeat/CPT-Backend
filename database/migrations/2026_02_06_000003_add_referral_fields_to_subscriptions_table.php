<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('referral_code_id')->nullable()->after('package_id');
            $table->unsignedBigInteger('referral_referrer_id')->nullable()->after('referral_code_id');
            $table->decimal('referral_credit_used', 10, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('referral_code_id');
            $table->dropColumn('referral_referrer_id');
            $table->dropColumn('referral_credit_used');
        });
    }
};
