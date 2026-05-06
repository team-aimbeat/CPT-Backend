<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'gateway')) {
                $table->string('gateway')->nullable();
            }

            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
            }

            if (!Schema::hasColumn('payments', 'gateway_subscription_id')) {
                $table->string('gateway_subscription_id')->nullable();
            }

            if (!Schema::hasColumn('payments', 'gateway_response')) {
                $table->json('gateway_response')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'gateway_response')) {
                $table->dropColumn('gateway_response');
            }

            if (Schema::hasColumn('payments', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }

            if (Schema::hasColumn('payments', 'gateway_subscription_id')) {
                $table->dropColumn('gateway_subscription_id');
            }

            if (Schema::hasColumn('payments', 'gateway')) {
                $table->dropColumn('gateway');
            }
        });
    }
};
