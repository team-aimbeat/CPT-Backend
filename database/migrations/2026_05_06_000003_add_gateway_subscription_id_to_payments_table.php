<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments') || Schema::hasColumn('payments', 'gateway_subscription_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway_subscription_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments') || !Schema::hasColumn('payments', 'gateway_subscription_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('gateway_subscription_id');
        });
    }
};
