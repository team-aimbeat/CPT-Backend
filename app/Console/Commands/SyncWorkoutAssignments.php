<?php

namespace App\Console\Commands;

use App\Http\Controllers\WorkoutController;
use App\Models\Workout;
use Illuminate\Console\Command;

class SyncWorkoutAssignments extends Command
{
    protected $signature = 'workouts:sync-assignments {--workout_id=} {--dry-run}';

    protected $description = 'Assign existing active matching workouts to already registered users when assignments are missing.';

    public function handle(): int
    {
        $query = Workout::with(['goal', 'level', 'workouttype'])
            ->where('status', 'active');

        if ($this->option('workout_id')) {
            $query->where('id', (int) $this->option('workout_id'));
        }

        $workoutCount = 0;
        $assignmentCount = 0;
        $dryRun = (bool) $this->option('dry-run');
        $syncer = app(WorkoutController::class);

        $query->orderBy('id')->chunkById(100, function ($workouts) use (&$workoutCount, &$assignmentCount, $dryRun, $syncer) {
            foreach ($workouts as $workout) {
                $workoutCount++;
                $created = $syncer->syncWorkoutAssignmentsForExistingUsers($workout, $dryRun);
                $assignmentCount += $created;

                if ($created > 0) {
                    $this->line("Workout #{$workout->id} matched {$created} missing assignment(s).");
                }
            }
        });

        $prefix = $dryRun ? '[Dry run] ' : '';
        $this->info($prefix . "Checked {$workoutCount} workout(s); {$assignmentCount} assignment(s) " . ($dryRun ? 'would be created.' : 'created.'));

        return Command::SUCCESS;
    }
}
