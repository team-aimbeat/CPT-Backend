<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DisableStaleWorkouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workouts:disable-stale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disables workouts that are status=1 and updated more than 2 hours ago.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
         
         $twoHoursAgo = Carbon::now()->subHours(2);
        // $twoHoursAgo = Carbon::now()->subMinutes(2);

        $updatedCount = DB::table('assign_workouts')
            ->where('status', 1) 
            ->where('updated_at', '<=', $twoHoursAgo) 
            ->where('disable', 0) 
            ->update([
                'disable' => 1,
                'updated_at' => Carbon::now() 
            ]);
            
            Log::info("Cron Job Check: Attempting to disable workouts. Updated count: " . $updatedCount);

        if ($updatedCount > 0) {
            $this->info("✅ Successfully disabled {$updatedCount} stale workouts.");
        } else {
            $this->info("ℹ️ No workouts needed to be disabled.");
        }

        return Command::SUCCESS;
    }
}