<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'gateway_subscription_id')) {
                $table->string('gateway_subscription_id')->nullable()->index();
            }

            if (!Schema::hasColumn('subscriptions', 'autopay_status')) {
                $table->string('autopay_status')->nullable()->index();
            }

            if (!Schema::hasColumn('subscriptions', 'autopay_cancelled_at')) {
                $table->timestamp('autopay_cancelled_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'autopay_cancelled_at')) {
                $table->dropColumn('autopay_cancelled_at');
            }

            if (Schema::hasColumn('subscriptions', 'autopay_status')) {
                $table->dropColumn('autopay_status');
            }

            if (Schema::hasColumn('subscriptions', 'gateway_subscription_id')) {
                $table->dropColumn('gateway_subscription_id');
            }
        });
    }
};
