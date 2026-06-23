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
            if (!Schema::hasColumn('subscriptions', 'trial_start_at')) {
                $table->timestamp('trial_start_at')->nullable()->after('autopay_cancelled_at');
            }

            if (!Schema::hasColumn('subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->index()->after('trial_start_at');
            }

            if (!Schema::hasColumn('subscriptions', 'billing_starts_at')) {
                $table->timestamp('billing_starts_at')->nullable()->index()->after('trial_ends_at');
            }

            if (!Schema::hasColumn('subscriptions', 'mandate_authorized_at')) {
                $table->timestamp('mandate_authorized_at')->nullable()->after('billing_starts_at');
            }

            if (!Schema::hasColumn('subscriptions', 'last_payment_failed_at')) {
                $table->timestamp('last_payment_failed_at')->nullable()->after('mandate_authorized_at');
            }

            if (!Schema::hasColumn('subscriptions', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('last_payment_failed_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            foreach ([
                'failure_reason',
                'last_payment_failed_at',
                'mandate_authorized_at',
                'billing_starts_at',
                'trial_ends_at',
                'trial_start_at',
            ] as $column) {
                if (Schema::hasColumn('subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
