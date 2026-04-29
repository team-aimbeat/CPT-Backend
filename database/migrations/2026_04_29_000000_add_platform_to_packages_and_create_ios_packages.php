<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('packages', 'package_type')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->string('package_type')->nullable()->after('status');
            });
        }

        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'platform')) {
                $table->string('platform')->default('android')->after('package_type');
            }

            if (!Schema::hasColumn('packages', 'android_package_id')) {
                $table->unsignedBigInteger('android_package_id')->nullable()->after('platform');
            }
        });

        DB::table('packages')
            ->whereNull('platform')
            ->update(['platform' => 'android']);

        $packages = DB::table('packages')
            ->where('platform', 'android')
            ->where(function ($query) {
                $query->whereNull('offer_enabled')
                    ->orWhere('offer_enabled', 0);
            })
            ->where(function ($query) {
                $query->whereNull('offer_same_access_count')
                    ->orWhere('offer_same_access_count', '<=', 0);
            })
            ->where(function ($query) {
                $query->whereNull('offer_free_access_count')
                    ->orWhere('offer_free_access_count', '<=', 0);
            })
            ->where(function ($query) {
                $query->whereNull('name')
                    ->orWhere(function ($query) {
                        $query->where('name', 'not like', '%member%')
                            ->where('name', 'not like', '%person%')
                            ->where('name', 'not like', '%family%')
                            ->where('name', 'not like', '%couple%')
                            ->where('name', 'not like', '%2p%')
                            ->where('name', 'not like', '%4p%');
                    });
            })
            ->get();

        foreach ($packages as $package) {
            $exists = DB::table('packages')
                ->where('platform', 'ios')
                ->where(function ($query) use ($package) {
                    $query->where('android_package_id', $package->id)
                        ->orWhere(function ($query) use ($package) {
                            $query->where('name', $package->name)
                                ->where('duration_unit', $package->duration_unit)
                                ->where('duration', $package->duration)
                                ->where('package_type', $package->package_type);
                        });
                })
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('packages')->insert([
                'name' => $package->name,
                'duration_unit' => $package->duration_unit,
                'duration' => $package->duration,
                'price' => $package->price === null ? null : round(((float) $package->price) * 1.15, 2),
                'description' => $package->description,
                'status' => $package->status,
                'package_type' => $package->package_type,
                'platform' => 'ios',
                'android_package_id' => $package->id,
                'offer_enabled' => 0,
                'offer_type' => null,
                'offer_access_days' => null,
                'offer_max_redemptions' => null,
                'offer_same_access_count' => null,
                'offer_free_access_count' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('packages')
            ->where('platform', 'ios')
            ->whereNotNull('android_package_id')
            ->delete();

        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'android_package_id')) {
                $table->dropColumn('android_package_id');
            }

            if (Schema::hasColumn('packages', 'platform')) {
                $table->dropColumn('platform');
            }
        });
    }
};
