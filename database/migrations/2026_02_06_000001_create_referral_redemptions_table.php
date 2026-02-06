<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referral_code_id');
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_user_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->decimal('reward_amount', 10, 2)->default(0);
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_redemptions');
    }
};
