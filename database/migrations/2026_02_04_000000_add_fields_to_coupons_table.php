<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('type')->default('free_access')->after('code');
            $table->decimal('value', 10, 2)->nullable()->after('type');
            $table->integer('access_days')->nullable()->after('value');
            $table->integer('max_redemptions')->nullable()->after('access_days');
            $table->integer('per_user_limit')->nullable()->after('max_redemptions');
            $table->date('valid_from')->nullable()->after('per_user_limit');
            $table->date('valid_to')->nullable()->after('valid_from');
            $table->boolean('first_purchase_only')->default(false)->after('valid_to');
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'value',
                'access_days',
                'max_redemptions',
                'per_user_limit',
                'valid_from',
                'valid_to',
                'first_purchase_only',
            ]);
        });
    }
};
