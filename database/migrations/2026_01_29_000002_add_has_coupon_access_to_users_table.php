<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'has_coupon_access')) {
                $table->boolean('has_coupon_access')->default(false)->after('is_subscribe');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'has_coupon_access')) {
                $table->dropColumn('has_coupon_access');
            }
        });
    }
};
