<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workout;
use App\Models\AssignWorkout;
use App\Models\WorkoutCompletion;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Traits\SubscriptionTrait;
use App\Http\Resources\UserDetailResource;
use App\Models\UserOtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{
    use SubscriptionTrait;
    
   
    
    
    
    // public function register(UserRequest $request)
    //     {
    //         DB::beginTransaction();
        
    //         try {
        
    //             /* -------------------------
    //              | 1. USER CREATE
    //              |--------------------------*/
    //             $input = $request->all();
    //             info('register_request: ' . json_encode($input));
        
    //             $password = $input['password'];
    //             $input['user_type'] = $input['user_type'] ?? 'user';
    //             $input['status'] = $input['status'] ?? 'pending';
    //             $input['password'] = Hash::make($password);
        
    //             if (request('player_id') === 'nil') {
    //                 $input['player_id'] = null;
    //             }
        
    //             $input['display_name'] = trim($input['first_name'] . ' ' . $input['last_name']);
        
    //             $user = User::create($input);
    //             $user->assignRole($input['user_type']);
        
    //             /* -------------------------
    //              | 2. USER PROFILE CREATE
    //              |--------------------------*/
    //             $userProfile = null;
    //             if ($request->filled('user_profile')) {
    //                 $userProfile = $user->userProfile()->create($request->user_profile);
    //             }
        
    //             /* -------------------------
    //              | 3. ASSIGN FIRST WORKOUT CYCLE
    //              |--------------------------*/
    //             $assignedWorkouts = collect();
        
    //             if ($userProfile) {
        
    //                 $levelId       = $userProfile->workout_level ?? null;
    //                 $goalId        = $userProfile->goal ?? null;
    //                 $workoutTypeId = $userProfile->workout_mode ?? null;
        
    //                 if ($levelId && $goalId && $workoutTypeId) {
        
    //                     $cycleNo = 1; // FIRST CYCLE (REGISTER)
        
    //                     $workoutIds = Workout::where('level_id', $levelId)
    //                         ->where('goal_id', $goalId)
    //                         ->where('workout_type_id', $workoutTypeId)
    //                         ->where('status', 'active')
    //                         ->pluck('id');
        
    //                     if ($workoutIds->isNotEmpty()) {
        
    //                         $now = now();
    //                         $insertData = [];
        
    //                         foreach ($workoutIds as $workoutId) {
    //                             $insertData[] = [
    //                                 'user_id'       => $user->id,
    //                                 'workout_id'    => $workoutId,
    //                                 'cycle_no'      => $cycleNo,
    //                                 'status'        => 0, // pending
    //                                 'assigned_from' => 'register',
    //                                 'created_at'    => $now,
    //                                 'updated_at'    => $now,
    //                             ];
    //                         }
        
    //                         DB::table('assign_workouts')->insert($insertData);
        
    //                         $assignedWorkouts = DB::table('assign_workouts')
    //                             ->where('user_id', $user->id)
    //                             ->where('cycle_no', $cycleNo)
    //                             ->get();
    //                     }
    //                 }
    //             }
        
    //             /* -------------------------
    //              | 4. TOKEN + MEDIA
    //              |--------------------------*/
    //             $user->api_token = $user->createToken('auth_token')->plainTextToken;
    //             $user->profile_image = getSingleMedia($user, 'profile_image', null);
        
    //             unset($user->roles);
        
    //             /* -------------------------
    //              | 5. RESPONSE BUILD
    //              |--------------------------*/
    //             $user->setHidden(['userProfile']);
        
    //             $userData = $user->toArray();
    //             $userData['user_profile_data'] = $userProfile ? $userProfile->toArray() : null;
    //             $userData['assigned_workouts'] = $assignedWorkouts->toArray();
        
    //             $message = __('message.save_form', [
    //                 'form' => __('message.' . $input['user_type'])
    //             ]);
        
    //             DB::commit();
        
    //             return json_custom_response([
    //                 'success' => true,
    //                 'message' => $message,
    //                 'data' => $userData
    //             ]);
        
    //         } catch (\Throwable $e) {
        
    //             DB::rollBack();
        
    //             Log::error('Register Error', [
    //                 'error' => $e->getMessage(),
    //                 'trace' => $e->getTraceAsString()
    //             ]);
        
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Registration failed. Please try again.'
    //             ], 500);
    //         }
    //     }




    public function register(UserRequest $request)
{
    DB::beginTransaction();

    try {

        /* -------------------------
         | 1. USER CREATE
         |--------------------------*/
        $input = $request->all();

        $password = $input['password'];
        $input['user_type'] = $input['user_type'] ?? 'user';
        $input['status'] = $input['status'] ?? 'pending';
        $input['password'] = Hash::make($password);

        if (request('player_id') === 'nil') {
            $input['player_id'] = null;
        }

        $input['display_name'] = trim($input['first_name'] . ' ' . $input['last_name']);

        $user = User::create($input);
        $user->assignRole($input['user_type']);

        /* -------------------------
         | 2. USER PROFILE
         |--------------------------*/
        $userProfile = null;
        if ($request->filled('user_profile')) {
            $userProfile = $user->userProfile()->create($request->user_profile);
        }

        /* -------------------------
         | 3. ASSIGN FIRST WORKOUT CYCLE (FIXED)
         |--------------------------*/
        $assignedWorkouts = collect();

        if ($userProfile) {

            $levelId       = $userProfile->workout_level;
            $goalId        = $userProfile->goal;
            $workoutTypeId = $userProfile->workout_mode;

            if ($levelId && $goalId && $workoutTypeId) {

                $cycleNo = 1; // FIRST CYCLE

                // 🔒 SAFETY: make sure no active cycle exists
                DB::table('assign_workouts')
                    ->where('user_id', $user->id)
                    ->update(['is_active' => 0]);

                $workoutIds = Workout::where('level_id', $levelId)
                    ->where('goal_id', $goalId)
                    ->where('workout_type_id', $workoutTypeId)
                    ->where('status', 'active')
                    ->pluck('id');

                if ($workoutIds->isNotEmpty()) {

                    $now = now();
                    $insertData = [];

                    foreach ($workoutIds as $workoutId) {
                        $insertData[] = [
                            'user_id'       => $user->id,
                            'workout_id'    => $workoutId,
                            'cycle_no'      => $cycleNo,
                            'status'        => 0, // pending
                            'is_active'     => 1, // ✅ VERY IMPORTANT
                            'assigned_from' => 'register',
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ];
                    }

                    DB::table('assign_workouts')->insert($insertData);

                    $assignedWorkouts = DB::table('assign_workouts')
                        ->where('user_id', $user->id)
                        ->where('cycle_no', $cycleNo)
                        ->get();
                }
            }
        }

        /* -------------------------
         | 4. TOKEN
         |--------------------------*/
        $user->api_token = $user->createToken('auth_token')->plainTextToken;
        $user->profile_image = getSingleMedia($user, 'profile_image', null);

        unset($user->roles);

        /* -------------------------
         | 5. RESPONSE
         |--------------------------*/
        $user->setHidden(['userProfile']);

        $userData = $user->toArray();
        $userData['user_profile_data'] = $userProfile ? $userProfile->toArray() : null;
        $userData['assigned_workouts'] = $assignedWorkouts->toArray();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $userData
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Register Error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Registration failed'
        ], 500);
    }
}

        
        
        
//         public function updateWorkoutMode(Request $request)
// {
//     DB::beginTransaction();

//     try {

//         /* ---------------------------------
//          | 1. AUTH USER
//          |----------------------------------*/
//         $user = auth('sanctum')->user();

//         if (!$user) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Unauthorized'
//             ], 401);
//         }

//         /* ---------------------------------
//          | 2. VALIDATION
//          |----------------------------------*/
//         $request->validate([
//             'workout_mode' => 'required|exists:workout_types,id'
//         ]);

//         $profile = $user->userProfile;

//         if (!$profile) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'User profile not found'
//             ], 404);
//         }

//         $oldMode = $profile->workout_mode;
//         $newMode = $request->workout_mode;

//         // Same mode → no action
//         if ($oldMode == $newMode) {
//             return response()->json([
//                 'success' => true,
//                 'message' => 'Workout mode already active'
//             ]);
//         }

//         /* ---------------------------------
//          | 3. UPDATE PROFILE
//          |----------------------------------*/
//         $profile->update([
//             'workout_mode' => $newMode
//         ]);

//         /* ---------------------------------
//          | 4. CHECK IF SAME PLAN EXISTS (RESUME LOGIC)
//          |----------------------------------*/
//         $existingCycle = DB::table('assign_workouts')
//             ->join('workouts', 'workouts.id', '=', 'assign_workouts.workout_id')
//             ->where('assign_workouts.user_id', $user->id)
//             ->where('workouts.workout_type_id', $newMode)
//             ->where('workouts.level_id', $profile->workout_level)
//             ->where('workouts.goal_id', $profile->goal)
//             ->orderByDesc('assign_workouts.cycle_no')
//             ->select('assign_workouts.cycle_no')
//             ->first();

//         /* ---------------------------------
//          | 5. INACTIVATE CURRENT PENDING WORKOUTS
//          |----------------------------------*/
//         DB::table('assign_workouts')
//             ->where('user_id', $user->id)
//             ->where('status', 0) // pending only
//             ->update([
//                 'status' => 2, // inactive
//                 'updated_at' => now()
//             ]);

//         /* ---------------------------------
//          | 6A. RESUME OLD CYCLE (IF EXISTS)
//          |----------------------------------*/
//         if ($existingCycle) {

//             DB::table('assign_workouts')
//                 ->where('user_id', $user->id)
//                 ->where('cycle_no', $existingCycle->cycle_no)
//                 ->where('status', '!=', 1) // completed untouched
//                 ->update([
//                     'status' => 0,
//                     'updated_at' => now()
//                 ]);

//             DB::commit();

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Workout plan resumed successfully',
//                 'workout_mode' => $newMode,
//                 'active_cycle' => $existingCycle->cycle_no,
//                 'action' => 'resumed'
//             ]);
//         }

//         /* ---------------------------------
//          | 6B. CREATE NEW CYCLE (FIRST TIME PLAN)
//          |----------------------------------*/
//         $lastCycle = DB::table('assign_workouts')
//             ->where('user_id', $user->id)
//             ->max('cycle_no');

//         $newCycle = ($lastCycle ?? 1) + 1;

//         $workoutIds = Workout::where('workout_type_id', $newMode)
//             ->where('level_id', $profile->workout_level)
//             ->where('goal_id', $profile->goal)
//             ->where('status', 'active')
//             ->pluck('id');

//         if ($workoutIds->isNotEmpty()) {

//             $now = now();
//             $insertData = [];

//             foreach ($workoutIds as $workoutId) {
//                 $insertData[] = [
//                     'user_id'       => $user->id,
//                     'workout_id'    => $workoutId,
//                     'cycle_no'      => $newCycle,
//                     'status'        => 0, // pending
//                     'assigned_from' => 'workout_mode_update',
//                     'created_at'    => $now,
//                     'updated_at'    => $now,
//                 ];
//             }

//             DB::table('assign_workouts')->insert($insertData);
//         }

//         DB::commit();

//         return response()->json([
//             'success' => true,
//             'message' => 'Workout mode updated successfully',
//             'workout_mode' => $newMode,
//             'active_cycle' => $newCycle,
//             'action' => 'new_cycle'
//         ]);

//     } catch (\Throwable $e) {

//         DB::rollBack();

//         \Log::error('Update Workout Mode Error', [
//             'error' => $e->getMessage()
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Failed to update workout mode'
//         ], 500);
//     }
// }
        
        
        
public function updateWorkoutMode(Request $request)
{
    DB::beginTransaction();

    try {

        /* ---------------------------------
         | 1. AUTH USER
         |----------------------------------*/
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        /* ---------------------------------
         | 2. VALIDATION
         |----------------------------------*/
        $request->validate([
            'workout_mode' => 'required|exists:workout_types,id'
        ]);

        $profile = $user->userProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found'
            ], 404);
        }

        $oldMode = $profile->workout_mode;
        $newMode = $request->workout_mode;

        if ($oldMode == $newMode) {
            return response()->json([
                'success' => true,
                'message' => 'Workout mode already active'
            ]);
        }

        /* ---------------------------------
         | 3. UPDATE PROFILE
         |----------------------------------*/
        $profile->update([
            'workout_mode' => $newMode
        ]);

        /* ---------------------------------
         | 4. FIND EXISTING CYCLE (RESUME)
         |----------------------------------*/
        $existingCycle = DB::table('assign_workouts')
            ->join('workouts', 'workouts.id', '=', 'assign_workouts.workout_id')
            ->where('assign_workouts.user_id', $user->id)
            ->where('workouts.workout_type_id', $newMode)
            ->where('workouts.level_id', $profile->workout_level)
            ->where('workouts.goal_id', $profile->goal)
            ->orderByDesc('assign_workouts.cycle_no')
            ->select('assign_workouts.cycle_no')
            ->first();

        /* ---------------------------------
         | 5. INACTIVATE ALL CYCLES (CRITICAL)
         |----------------------------------*/
        DB::table('assign_workouts')
            ->where('user_id', $user->id)
            ->update([
                'is_active' => 0,
                'status' => 2,
                'updated_at' => now()
            ]);

        /* ---------------------------------
         | 6A. RESUME OLD CYCLE
         |----------------------------------*/
        if ($existingCycle) {

            DB::table('assign_workouts')
                ->where('user_id', $user->id)
                ->where('cycle_no', $existingCycle->cycle_no)
                ->where('status', '!=', 1)
                ->update([
                    'status' => 0,
                    'disable' => 0,
                    'is_active' => 1, // ✅ IMPORTANT
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workout plan resumed successfully',
                'workout_mode' => $newMode,
                'active_cycle' => $existingCycle->cycle_no,
                'action' => 'resumed'
            ]);
        }

        /* ---------------------------------
         | 6B. CREATE NEW CYCLE
         |----------------------------------*/
        $lastCycle = DB::table('assign_workouts')
            ->where('user_id', $user->id)
            ->max('cycle_no');

        $newCycle = ($lastCycle ?? 0) + 1;

        $workoutIds = Workout::where('workout_type_id', $newMode)
            ->where('level_id', $profile->workout_level)
            ->where('goal_id', $profile->goal)
            ->where('status', 'active')
            ->pluck('id');

        if ($workoutIds->isNotEmpty()) {

            $now = now();
            $insertData = [];

            foreach ($workoutIds as $workoutId) {
                $insertData[] = [
                    'user_id'       => $user->id,
                    'workout_id'    => $workoutId,
                    'cycle_no'      => $newCycle,
                    'status'        => 0,
                    'disable'       => 0,
                    'is_active'     => 1, // ✅ IMPORTANT
                    'assigned_from' => 'workout_mode_update',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            DB::table('assign_workouts')->insert($insertData);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Workout mode updated successfully',
            'workout_mode' => $newMode,
            'active_cycle' => $newCycle,
            'action' => 'new_cycle'
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        \Log::error('Update Workout Mode Error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update workout mode'
        ], 500);
    }
}






// public function googleLoginOrRegister(Request $request)
// {
//     DB::beginTransaction();

//     try {

//         /* -------------------------------
//          | 1. VALIDATION
//          |--------------------------------*/
//         $request->validate([
//             'email' => 'required|email|max:255',
//             'provider_id' => 'required|string',
//             'login_type' => 'required|in:google',
//             'first_name' => 'nullable|string|max:255',
//             'last_name' => 'nullable|string|max:255',
//             'user_profile' => 'nullable|array',
//         ]);

//         $email = $request->email;
//         $providerId = $request->provider_id;

//         /* -------------------------------
//          | 2. FIND USER
//          |--------------------------------*/
//         $user = User::where('provider_id', $providerId)
//             ->orWhere('email', $email)
//             ->first();

//         $action = 'login';
//         $message = __('message.login_success');

//         /* -------------------------------
//          | 3. REGISTER (FIRST TIME)
//          |--------------------------------*/
//         if (!$user) {

//             $action = 'register';
//             $message = __('message.save_form', ['form' => __('message.user')]);

//             $username = explode('@', $email)[0];

//             $user = User::create([
//                 'email' => $email,
//                 'provider' => 'google',
//                 'provider_id' => $providerId,
//                 'first_name' => $request->first_name ?? $username,
//                 'last_name' => $request->last_name,
//                 'username' => $username,
//                 'display_name' => trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')),
//                 'user_type' => 'user',
//                 'status' => 'active',
//                 'login_type' => 'google',
//                 'password' => null,
//                 'email_verified_at' => now(),
//             ]);

//             $user->assignRole('user');
//         }
//         /* -------------------------------
//          | 4. LINK PROVIDER (OLD USER)
//          |--------------------------------*/
//         else if (empty($user->provider_id)) {
//             $user->update([
//                 'provider' => 'google',
//                 'provider_id' => $providerId,
//                 'login_type' => 'google',
//             ]);
//         }

//         /* -------------------------------
//          | 5. USER PROFILE (OPTIONAL)
//          |--------------------------------*/
//         $userProfile = null;

//         if ($request->filled('user_profile')) {
//             $userProfile = $user->userProfile()->updateOrCreate(
//                 ['user_id' => $user->id],
//                 $request->user_profile
//             );
//         }

//         /* -------------------------------
//          | 6. ASSIGN WORKOUTS (ONLY ON REGISTER)
//          |--------------------------------*/
//         $assignedWorkouts = collect();

//         if ($action === 'register' && $userProfile) {

//             $levelId = $userProfile->workout_level;
//             $goalId  = $userProfile->goal;
//             $modeId  = $userProfile->workout_mode;

//             if ($levelId && $goalId && $modeId) {

//                 $cycleNo = 1;

//                 $workoutIds = Workout::where('level_id', $levelId)
//                     ->where('goal_id', $goalId)
//                     ->where('workout_type_id', $modeId)
//                     ->where('status', 'active')
//                     ->pluck('id');

//                 if ($workoutIds->isNotEmpty()) {

//                     $now = now();
//                     $insertData = [];

//                     foreach ($workoutIds as $workoutId) {
//                         $insertData[] = [
//                             'user_id'       => $user->id,
//                             'workout_id'    => $workoutId,
//                             'cycle_no'      => $cycleNo,
//                             'status'        => 0,
//                             'assigned_from' => 'google_register',
//                             'created_at'    => $now,
//                             'updated_at'    => $now,
//                         ];
//                     }

//                     DB::table('assign_workouts')->insert($insertData);

//                     $assignedWorkouts = DB::table('assign_workouts')
//                         ->where('user_id', $user->id)
//                         ->where('cycle_no', $cycleNo)
//                         ->get();
//                 }
//             }
//         }

//         /* -------------------------------
//          | 7. TOKEN & RESPONSE
//          |--------------------------------*/
//         $user->api_token = $user->createToken('auth_token')->plainTextToken;
//         $user->profile_image = getSingleMedia($user, 'profile_image', null);

//         unset($user->roles);

//         $userData = $user->toArray();
//         $userData['user_profile_data'] = $userProfile ? $userProfile->toArray() : null;
//         $userData['assigned_workouts'] = $assignedWorkouts->toArray();

//         DB::commit();

//         return json_custom_response([
//             'message' => $message,
//             'action'  => $action,
//             'data'    => $userData
//         ]);

//     } catch (\Throwable $e) {

//         DB::rollBack();

//         \Log::error('Google Login/Register Error', [
//             'error' => $e->getMessage()
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Google login failed'
//         ], 500);
//     }
// }


    
  public function googleLoginOrRegister(Request $request)
{
    DB::beginTransaction();

    try {

        /* -------------------------------
         | 1. VALIDATION
         |--------------------------------*/
        $request->validate([
            'email' => 'required|email|max:255',
            'provider_id' => 'required|string',
            'login_type' => 'required|in:google',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'user_profile' => 'nullable|array',
        ]);

        $email = $request->email;
        $providerId = $request->provider_id;

        /* -------------------------------
         | 2. FIND USER
         |--------------------------------*/
        $user = User::where('provider_id', $providerId)
            ->orWhere('email', $email)
            ->first();

        $action = 'login';
        $message = __('message.login_success');

        /* -------------------------------
         | 3. REGISTER (FIRST TIME)
         |--------------------------------*/
        if (!$user) {

            $action = 'register';
            $message = __('message.save_form', ['form' => __('message.user')]);

            $username = explode('@', $email)[0];

            $user = User::create([
                'email' => $email,
                'provider' => 'google',
                'provider_id' => $providerId,
                'first_name' => $request->first_name ?? $username,
                'last_name' => $request->last_name,
                'username' => $username,
                'display_name' => trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')),
                'user_type' => 'user',
                'status' => 'active',
                'login_type' => 'google',
                'password' => null,
                'email_verified_at' => now(),
            ]);

            $user->assignRole('user');
        }
        /* -------------------------------
         | 4. LINK PROVIDER (OLD USER)
         |--------------------------------*/
        else if (empty($user->provider_id)) {
            $user->update([
                'provider' => 'google',
                'provider_id' => $providerId,
                'login_type' => 'google',
            ]);
        }

        /* -------------------------------
         | 5. USER PROFILE
         |--------------------------------*/
        $userProfile = null;

        if ($request->filled('user_profile')) {
            $userProfile = $user->userProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $request->user_profile
            );
        } else {
            $userProfile = $user->userProfile;
        }

        /* -------------------------------
         | 6. ASSIGN FIRST WORKOUT CYCLE (✅ FIXED)
         |--------------------------------*/
        $assignedWorkouts = DB::table('assign_workouts')
            ->where('user_id', $user->id)
            ->where('is_active', 1)
            ->get();

        $levelId = $userProfile->workout_level ?? null;
        $goalId  = $userProfile->goal ?? null;
        $modeId  = $userProfile->workout_mode ?? null;
        $canAssign = $levelId && $goalId && $modeId;

        if ($canAssign && $assignedWorkouts->isEmpty()) {

                $cycleNo = (int) DB::table('assign_workouts')
                    ->where('user_id', $user->id)
                    ->max('cycle_no') + 1;

                if ($cycleNo <= 0) {
                    $cycleNo = 1;
                }

                // 🔒 deactivate any previous cycles (safety)
                DB::table('assign_workouts')
                    ->where('user_id', $user->id)
                    ->update(['is_active' => 0]);

                $workoutIds = Workout::where('level_id', $levelId)
                    ->where('goal_id', $goalId)
                    ->where('workout_type_id', $modeId)
                    ->where('status', 'active')
                    ->pluck('id');

                if ($workoutIds->isNotEmpty()) {

                    $now = now();
                    $insertData = [];

                    foreach ($workoutIds as $workoutId) {
                        $insertData[] = [
                            'user_id'       => $user->id,
                            'workout_id'    => $workoutId,
                            'cycle_no'      => $cycleNo,
                            'status'        => 0,
                            'is_active'     => 1, // ✅ VERY IMPORTANT
                            'assigned_from' => $action === 'register' ? 'google_register' : 'google_login_recovery',
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ];
                    }

                    DB::table('assign_workouts')->insert($insertData);

                    $assignedWorkouts = DB::table('assign_workouts')
                        ->where('user_id', $user->id)
                        ->where('is_active', 1)
                        ->get();
                }
        }

        /* -------------------------------
         | 7. TOKEN & RESPONSE
         |--------------------------------*/
        $user->api_token = $user->createToken('auth_token')->plainTextToken;
        $user->profile_image = getSingleMedia($user, 'profile_image', null);

        unset($user->roles);

        $userData = $user->toArray();
        $userData['user_profile_data'] = $userProfile ? $userProfile->toArray() : null;
        $userData['assigned_workouts'] = $assignedWorkouts->toArray();

        DB::commit();

        return json_custom_response([
            'message' => $message,
            'action'  => $action,
            'data'    => $userData
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        \Log::error('Google Login/Register Error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Google login failed'
        ], 500);
    }
}
  
    
   
    
    
    

    
    



    public function updateAssignmentStatusByUserAndWorkout(Request $request)
    {
        
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'workout_id' => 'required|integer|exists:workouts,id',
        ]);
        
        $userId = $request->input('user_id');
        $workoutId = $request->input('workout_id');
        
       
        $updated = DB::table('assign_workouts')
                     ->where('user_id', $userId)
                     ->where('workout_id', $workoutId)
                     ->update([
                         'status' => 1, 
                         'updated_at' => Carbon::now(),
                     ]);
    
        if ($updated > 0) {
            return response()->json([
                'message' => 'Workout status updated successfully to 1.',
                'user_id' => $userId,
                'workout_id' => $workoutId
            ]);
        }
    
        return response()->json([
            'message' => 'Assignment record not found for this user and workout, or status was already 1.'
        ], 404); 
    }



    // public function getMonthlyAssignmentStatus(Request $request, $user_id)
    // {
       
    //     $startOfMonth = Carbon::now()->startOfMonth();
    //     $endOfMonth = Carbon::now()->endOfMonth();
    
        
    //     $assignments = AssignWorkout::where('user_id', $user_id)
    //         ->whereBetween('updated_at', [$startOfMonth, $endOfMonth]) 
    //         ->where('status', 1)
    //         ->orderBy('updated_at', 'desc') 
    //         ->get();
    
    //     if ($assignments->isEmpty()) {
    //         return response()->json([
    //             'message' => 'No assignments found for this user and workout in the current month.',
    //             'data' => []
    //         ], 404);
    //     }
    
    //     return response()->json([
    //         'message' => 'Monthly workout assignment status fetched successfully.',
    //         'month_range' => $startOfMonth->format('Y-m-d') . ' to ' . $endOfMonth->format('Y-m-d'),
    //         'total_records' => $assignments->count(),
    //         'data' => $assignments
    //     ]);
    // }
    
    
    public function getMonthlyAssignmentStatus(Request $request, $user_id)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
    
        $assignments = WorkoutCompletion::where('user_id', $user_id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->orderBy('created_at', 'asc')
            ->get();
    
        $completedDates = $assignments->map(function($item) {
            return $item->created_at->format('Y-m-d');
        })->unique()->values();
    
        return response()->json([
            'status' => true,
            'month_range' => $startOfMonth->format('Y-m-d') . ' to ' . $endOfMonth->format('Y-m-d'),
            'total_completed_days' => $completedDates->count(),
            'completed_dates' => $completedDates, 
            'data' => $assignments
        ]);
    }


    public function login(Request $request)
    {      
        info('login: '.json_encode($request->all()));
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            
            $user = Auth::user();

            if( $user->status == 'banned' ) {
                $message = __('message.account_banned');
                return json_message_response($message,400);
            }

            if(request('player_id') != null && request('player_id') != "nil"){
                $user->player_id = request('player_id');
            }
            $user->save();
            // $user->tokens('auth_token')->delete();
            $success = $user;
            $success['api_token'] = $user->createToken('auth_token')->plainTextToken;
            $success['profile_image'] = getSingleMedia($user, 'profile_image', null);
            
            unset($success['media']);

            return json_custom_response([ 'data' => $success ], 200 );
        } else{
            $message = __('auth.failed');
            
            return json_message_response($message,400);
        }
    }

    public function userDetail(Request $request)
    {
        $id = $request->id;

        $user = User::where('id',$id)->where('user_type', 'user')->first();
        
        if(empty($user)) {
            $message = __('message.not_found_entry', ['name' => __('message.user') ]);
            return json_message_response($message,400);   
        }

        $user_detail = new UserDetailResource($user);
        $response = [
            'data' => $user_detail,
            'subscription_detail' => $this->subscriptionPlanDetail($user->id),
        ];
        if( $user->player_id == "nil" ) {
            $user->player_id = NULL;
            $user->save();
        }
        return json_custom_response($response);

    }

    public function changePassword(Request $request)
    {
        $user = User::where('id',auth()->id())->first();

        if($user == "") {
            $message = __('message.not_found_entry', ['name' => __('message.user') ]);
            return json_message_response($message,400);   
        }
           
        $hashedPassword = $user->password;

        $match = Hash::check($request->old_password, $hashedPassword);

        $same_exits = Hash::check($request->new_password, $hashedPassword);
        if ($match)
        {
            if($same_exits){
                $message = __('message.old_new_pass_same');
                return json_message_response($message,400);
            }

			$user->fill([
                'password' => Hash::make($request->new_password)
            ])->save();
            
            $message = __('message.password_change');
            return json_message_response($message,200);
        }
        else
        {
            $message = __('message.valid_password');
            return json_message_response($message,400);
        }
    }
    
    
    
    
    
    public function updateProfile(UserRequest $request)
{
    DB::beginTransaction();

    try {

        /* -------------------------
         | 1. AUTH USER
         |--------------------------*/
        $user = auth('sanctum')->user();

        if ($request->filled('id')) {
            $user = User::find($request->id);
        }

        if (!$user) {
            return json_message_response(__('message.no_record_found'), 400);
        }

        /* -------------------------
         | 2. DEMO CHECK
         |--------------------------*/
        if (env('APP_DEMO') && in_array($user->email, ['smith@gmail.com'])) {
            return json_custom_response([
                'message' => __('message.demo_permission_denied'),
                'status'  => true
            ]);
        }

        /* -------------------------
         | 3. USER BASIC UPDATE
         |--------------------------*/
        $user->fill($request->all())->update();

        /* -------------------------
         | 4. PROFILE IMAGE
         |--------------------------*/
        if ($request->hasFile('profile_image')) {

            $file = $request->file('profile_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $targetFolder = public_path('storage/uploads/exercise_gif');

            $file->move($targetFolder, $filename);

            $user->profile_image = 'uploads/exercise_gif/' . $filename;
            $user->save();
        }

        /* -------------------------
         | 5. USER PROFILE UPDATE
         |--------------------------*/
        $user->refresh();
        $profile = $user->userProfile;

        $oldLevel = $profile->workout_level ?? null;
        $oldGoal  = $profile->goal ?? null;
        $oldMode  = $profile->workout_mode ?? null;

        if ($profile && $request->filled('user_profile')) {
            $profile->fill($request->user_profile)->update();
        } elseif ($request->filled('user_profile')) {
            $profile = $user->userProfile()->create($request->user_profile);
        }

        $profile->refresh();

        /* -------------------------
         | 6. CHECK WORKOUT PLAN CHANGE
         |--------------------------*/
        $newLevel = $profile->workout_level ?? null;
        $newGoal  = $profile->goal ?? null;
        $newMode  = $profile->workout_mode ?? null;

        $workoutPlanChanged =
            ($oldLevel != $newLevel) ||
            ($oldGoal  != $newGoal) ||
            ($oldMode  != $newMode);

        /* -------------------------
         | 7. ASSIGN / REASSIGN WORKOUTS
         |--------------------------*/
        if ($workoutPlanChanged && $newLevel && $newGoal && $newMode) {

            // 🔹 Inactivate ONLY pending workouts (completed untouched)
            DB::table('assign_workouts')
                ->where('user_id', $user->id)
                ->where('status', 0) // pending
                ->update([
                    'status' => 2, // inactive
                    'updated_at' => now(),
                ]);

            // 🔹 New cycle number
            $lastCycle = DB::table('assign_workouts')
                ->where('user_id', $user->id)
                ->max('cycle_no');

            $newCycle = ($lastCycle ?? 0) + 1;

            // 🔹 Fetch workouts for new plan
            $workoutIds = Workout::where('level_id', $newLevel)
                ->where('goal_id', $newGoal)
                ->where('workout_type_id', $newMode)
                ->where('status', 'active')
                ->pluck('id');

            if ($workoutIds->isNotEmpty()) {

                $now = now();
                $insertData = [];

                foreach ($workoutIds as $workoutId) {
                    $insertData[] = [
                        'user_id'       => $user->id,
                        'workout_id'    => $workoutId,
                        'cycle_no'      => $newCycle,
                        'status'        => 0, // pending
                        'assigned_from' => 'profile_update',
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }

                DB::table('assign_workouts')->insert($insertData);
            }
        }

        /* -------------------------
         | 8. RESPONSE
         |--------------------------*/
        $user->refresh();
        unset($user['media']);

        $user_resource = new UserDetailResource($user);

        DB::commit();

        return json_custom_response([
            'data'    => $user_resource,
            'message' => __('message.updated')
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Profile Update Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Profile update failed'
        ], 500);
    }
}
    
    
    // public function updateProfile(UserRequest $request)
    // {   
    //     DB::beginTransaction();
    
    //     try {
    
    //         /* -------------------------
    //          | 1. USER FETCH
    //          |--------------------------*/
    //         $user = auth()->user();
    
    //         if ($request->filled('id')) {
    //             $user = User::find($request->id);
    //         }
    
    //         if (!$user) {
    //             return json_message_response(__('message.no_record_found'), 400);
    //         }
    
    //         /* -------------------------
    //          | 2. DEMO CHECK
    //          |--------------------------*/
    //         if (env('APP_DEMO')) {
    //             if (in_array($user->email, ['smith@gmail.com'])) {
    //                 return json_custom_response([
    //                     'message' => __('message.demo_permission_denied'),
    //                     'status'  => true
    //                 ]);
    //             }
    //         }
    
    //         /* -------------------------
    //          | 3. USER BASIC UPDATE
    //          |--------------------------*/
    //         $user->fill($request->all())->update();
    
    //         /* -------------------------
    //          | 4. PROFILE IMAGE
    //          |--------------------------*/
    //         if ($request->hasFile('profile_image')) {
    
    //             $file = $request->file('profile_image');
    //             $filename = time() . '_' . $file->getClientOriginalName();
    //             $targetFolder = public_path('storage/uploads/exercise_gif');
    
    //             $file->move($targetFolder, $filename);
    
    //             $user->profile_image = 'uploads/exercise_gif/' . $filename;
    //             $user->save();
    //         }
    
    //         /* -------------------------
    //          | 5. USER PROFILE UPDATE
    //          |--------------------------*/
    //         $user->refresh();
    
    //         $profile = $user->userProfile;
    
    //         $oldLevel = $profile->workout_level ?? null;
    //         $oldGoal  = $profile->goal ?? null;
    //         $oldMode  = $profile->workout_mode ?? null;
    
    //         if ($profile && $request->filled('user_profile')) {
    //             $profile->fill($request->user_profile)->update();
    //         } elseif ($request->filled('user_profile')) {
    //             $profile = $user->userProfile()->create($request->user_profile);
    //         }
    
    //         $profile->refresh();
    
    //         /* -------------------------
    //          | 6. WORKOUT CHANGE CHECK
    //          |--------------------------*/
    //         $newLevel = $profile->workout_level ?? null;
    //         $newGoal  = $profile->goal ?? null;
    //         $newMode  = $profile->workout_mode ?? null;
    
    //         $workoutPlanChanged =
    //             ($oldLevel != $newLevel) ||
    //             ($oldGoal  != $newGoal) ||
    //             ($oldMode  != $newMode);
    
    //         /* -------------------------
    //          | 7. SAFE REASSIGN WORKOUTS
    //          |--------------------------*/
    //         if ($workoutPlanChanged && $newLevel && $newGoal && $newMode) {
    
    //             // ðŸ”¹ Inactivate old pending workouts
    //             DB::table('assign_workouts')
    //                 ->where('user_id', $user->id)
    //                 ->where('status', '!=', 1) // completed safe
    //                 ->update([
    //                     'status' => 2, // inactive
    //                     'updated_at' => now(),
    //                 ]);
    
    //             // ðŸ”¹ Get next cycle number
    //             $lastCycle = DB::table('assign_workouts')
    //                 ->where('user_id', $user->id)
    //                 ->max('cycle_no');
    
    //             $newCycle = ($lastCycle ?? 1) + 1;
    
    //             // ðŸ”¹ Fetch matching workouts
    //             $workoutIds = Workout::where('level_id', $newLevel)
    //                 ->where('goal_id', $newGoal)
    //                 ->where('workout_type_id', $newMode)
    //                 ->where('status', 'active')
    //                 ->pluck('id');
    
    //             if ($workoutIds->isNotEmpty()) {
    
    //                 $existingWorkoutIds = DB::table('assign_workouts')
    //                     ->where('user_id', $user->id)
    //                     ->pluck('workout_id')
    //                     ->toArray();
    
    //                 $now = now();
    //                 $insertData = [];
    
    //                 foreach ($workoutIds as $workoutId) {
    //                     if (in_array($workoutId, $existingWorkoutIds)) {
    //                         continue; // avoid duplicates
    //                     }
    
    //                     $insertData[] = [
    //                         'user_id'       => $user->id,
    //                         'workout_id'    => $workoutId,
    //                         'cycle_no'      => $newCycle,
    //                         'status'        => 0, // pending
    //                         'assigned_from' => 'profile_update',
    //                         'created_at'    => $now,
    //                         'updated_at'    => $now,
    //                     ];
    //                 }
    
    //                 if (!empty($insertData)) {
    //                     DB::table('assign_workouts')->insert($insertData);
    //                 }
    //             }
    //         }
    
    //         /* -------------------------
    //          | 8. RESPONSE
    //          |--------------------------*/
    //         $user->refresh();
    //         unset($user['media']);
    
    //         $user_resource = new UserDetailResource($user);
    
    //         DB::commit();
    
    //         return json_custom_response([
    //             'data'    => $user_resource,
    //             'message' => __('message.updated')
    //         ]);
    
    //     } catch (\Throwable $e) {
    
    //         DB::rollBack();
    
    //         Log::error('Profile Update Error', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Profile update failed'
    //         ], 500);
    //     }
    // }

    

  

    public function logout(Request $request)
    {
        $user = Auth::user();
        if($request->is('api*'))
        {
            $user->player_id = null;
            $user->save();
            $user->currentAccessToken()->delete();
            return json_message_response('Logout successfully');
        }
    }

    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $response = Password::sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => __($response), 'status' => true], 200)
            : response()->json(['message' => __($response), 'status' => false], 400);
    }
    
    public function socialMailLogin(Request $request)
    {
        $input = $request->all();
        info('mail -register: '.json_encode($input));
        $user_data = User::where('email',$input['email'])->first();
        
        if( $user_data != null ) {
            if( !in_array($user_data->user_type, ['admin',request('user_type')] )) {
                $message = __('auth.failed');
                return json_message_response($message,400);
            }

            if( $user_data->status == 'banned' ) {
                $message = __('message.account_banned');
                return json_message_response($message,400);
            }
        
            if( !isset($user_data->login_type) || $user_data->login_type  == '' ) {
                if( in_array($request->login_type, ['google', 'apple'] ))
                {
                    $message = __('validation.unique',['attribute' => 'email' ]);
                    return json_message_response($message,400);
                }
            }
            $message = __('message.login_success');
        }
        else
        {            
            $validator = Validator::make($input,[
                'email' => 'required|email|unique:users,email',
                'username'  => 'required|unique:users,username',
                'phone_number' => 'nullable|max:20|unique:users,phone_number',
            ]);

            if ( $validator->fails() ) {
                $data = [
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'all_message' =>  $validator->errors()
                ];
    
                return json_custom_response($data, 422);
            }

            $password = !empty($input['accessToken']) ? $input['accessToken'] : $input['email'];

            $input['display_name'] = $input['first_name']." ".$input['last_name'];
            $input['password'] = Hash::make($password);
            $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'user';
            if( request('player_id') == "nil"){
                $input['player_id'] = NULL;
            }
            $user = User::create($input);
            
            $user->assignRole($input['user_type']);

            $user_data = User::where('id',$user->id)->first();
            $message = __('message.save_form',['form' => $input['user_type'] ]);
        }
        
        // $user_data->tokens('auth_token')->delete();
        $user_data['api_token'] = $user_data->createToken('auth_token')->plainTextToken;
        $user_data['profile_image'] = getSingleMedia($user_data, 'profile_image', null);

        $response = [
            'status'    => true,
            'message'   => $message,
            'data'      => $user_data
        ];
        return json_custom_response($response);
    }

    public function socialOTPLogin(Request $request)
    {
        $input = $request->all();
        info('otp -register: '.json_encode($input));
        $user_data = User::where('username', $input['username'])->where('login_type','mobile')->first();
                
        if( $user_data != null )
        {
            if( !in_array($user_data->user_type, ['admin',request('user_type')] )) {
                $message = __('auth.failed');
                return json_message_response($message,400);
            }

            if( $user_data->status == 'banned' ) {
                $message = __('message.account_banned');
                return json_message_response($message,400);
            }
        
            if( !isset($user_data->login_type) || $user_data->login_type  == '' )
            {
                $message = __('validation.unique',['attribute' => 'username' ]);
                return json_message_response($message,400);
            }
            $message = __('message.login_success');
        }
        else
        {
            if($request->login_type === 'mobile' && $user_data == null ){
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => false
                ];
                return json_custom_response($otp_response);
            }
            
            $validator = Validator::make($input,[
                'email' => 'required|email|unique:users,email',
                'username'  => 'required|unique:users,username',
                'phone_number' => 'max:20|unique:users,phone_number',
            ]);

            if ( $validator->fails() ) {
                $data = [
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'all_message' =>  $validator->errors()
                ];
    
                return json_custom_response($data, 422);
            }

            $password = !empty($input['accessToken']) ? $input['accessToken'] : $input['email'];
            if( request('player_id') == "nil"){
                $input['player_id'] = NULL;
            }
            $input['display_name'] = $input['first_name']." ".$input['last_name'];
            $input['password'] = Hash::make($password);
            $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'user';
            $user = User::create($input);

            $user->assignRole($input['user_type']);

            $user_data = User::where('id',$user->id)->first();
            $message = __('message.save_form',['form' => $input['user_type'] ]);
        }
        // $user_data->tokens('auth_token')->delete();
        $user_data['api_token'] = $user_data->createToken('auth_token')->plainTextToken;
        $user_data['profile_image'] = getSingleMedia($user_data, 'profile_image', null);

        $response = [
            'status'    => true,
            'message'   => $message,
            'data'      => $user_data
        ];
        return json_custom_response($response);
    }

    public function updateUserStatus(Request $request)
    {
        $user_id = $request->id ?? auth()->user()->id;
        
        $user = User::where('id',$user_id)->first();

        if($user == "") {
            $message = __('message.not_found_entry', ['name' => __('message.user') ]);
            return json_message_response($message,400);
        }
        if($request->has('status')) {
            $user->status = $request->status;
        }
        
        $user->save();

        
        $user_resource = new UserResource($user);
        
        $message = __('message.update_form',['form' => __('message.status') ]);
        $response = [
            'data'      => $user_resource,
            'message'   => $message
        ];
        return json_custom_response($response);
    }

    public function getAppSetting(Request $request)
    {
        if($request->has('id') && isset($request->id)){
            $data = AppSetting::where('id',$request->id)->first();
        } else {
            $data = AppSetting::first();
        }

        $app_version = [
            'android_force_update'  => SettingData('APPVERSION', 'APPVERSION_ANDROID_FORCE_UPDATE'),
            'android_version_code'  => SettingData('APPVERSION', 'APPVERSION_ANDROID_VERSION_CODE'),
            'playstore_url'         => SettingData('APPVERSION', 'APPVERSION_PLAYSTORE_URL'),
            'ios_force_update'      => SettingData('APPVERSION', 'APPVERSION_IOS_FORCE_UPDATE'),
            'ios_version'           => SettingData('APPVERSION', 'APPVERSION_IOS_VERSION'),
            'appstore_url'          => SettingData('APPVERSION', 'APPVERSION_APPSTORE_URL'),
        ];

        $data['app_version'] = $app_version;

        return json_custom_response($data);
    }

    public function deleteUserAccount(Request $request)
    {
        $id = auth()->id();
        $user = User::where('id', $id)->first();
        if( env('APP_DEMO') ) {
            if( in_array($user->email, [ 'smith@gmail.com' ] )) {
                $message = __('message.demo_permission_denied'); 
                return json_custom_response(['message' => $message, 'status' => true]);
            }
        }
        $message = __('message.not_found_entry',['name' => __('message.account') ]);

        if( $user != '' ) {
            $user->delete();
            $message = __('message.account_deleted');
        }
        
        return json_custom_response(['message'=> $message, 'status' => true]);
    }

    public function userProfileDetail(Request $request)
    {
        $user = auth()->user();

        $user = User::where('id',$user->id)->where('user_type', 'user')->first();
        
        if(empty($user)) {
            $message = __('message.not_found_entry', ['name' => __('message.user') ]);
            return json_message_response($message,400);   
        }

        $user_detail = new UserDetailResource($user);
        $response = [
            'data' => $user_detail,
            'subscription_detail' => $this->subscriptionPlanDetail($user->id),
        ];
        
        return json_custom_response($response);
    }


    public function mailOtpSubmit(Request $request)
    {

        $request->validate([
            'email' => 'required'
        ]);


        try {
            $isUser = User::where('email', '=', $request->email)->first();

            $userOtp = UserOtp::where("email", $request->email)->latest()->first();

            $now = now();

            if (!$userOtp || ($userOtp && $now->isAfter($userOtp->expire_at))) {
                $userOtp = UserOtp::create([
                    "user_id" => $isUser ? $isUser->id : null,
                    "email" => $request->email,
                    "otp" => rand(000001, 999999),
                    "expire_at" => $now->addMinutes(10)
                ]);
            }

            $messageText = "\n\n";
            $messageText .= "OTP is " . $userOtp->otp . " for your verification on " . env('APP_NAME') . ". This OTP can be used only once and is valid for 10 min only\n";

            Mail::raw($messageText, function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Login Otp');
            });
        } catch (\Exception $e) {
            return json_custom_response(['status' => false, 'message' => 'Please try again!']);
            // return dd($e);
        }
        return json_custom_response(['status' => true, 'message' => 'OTP Mail sent successfully']);
    }

    public function mailOtpCheck(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'otp' => 'required',
        ]);


        try {

            $userOtp = UserOtp::where(["email" => $request->email, "otp" => $request->otp])->latest()->first();

            $now = now();
            $response = [];
            if (!$userOtp) {
                $response["status"] = false;
                $response["message"] = 'Your Otp Not Valid';
                return response()->json($response);
            } else if ($userOtp && $now->isAfter($userOtp->expire_at)) {
                $response["status"] = false;
                $response["message"] = 'Your Otp has been expired';
                return response()->json($response);
            }

            $isUser = User::where('email', '=', $request->email)->first();

            if ($isUser) {
                if($isUser->status == 'active'){
                    $userOtp->expire_at = now();
                    $userOtp->update();
                    
                    $response["status"] = true;
                    $response["api_token"] = $isUser->createToken('auth_token')->plainTextToken;
                    $response["message"] = 'Login successfully';
                    return response()->json($response);
                }else{
                    $response["status"] = false;
                    $response["message"] = 'User inactive';
                    return response()->json($response);
                }
            }else{
                $response["status"] = true;
                $response["get_name"] = true;
                $response["message"] = 'OTP valid successfully';
                return json_custom_response($response);
            }
        } catch (\Exception $e) {
            return json_custom_response(['status' => false, 'message' => 'Please try again!']);
            // return dd($e);
        }
        return json_custom_response(['status' => true, 'message' => 'Something wrong']);
    }

    public function registerByMailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'otp' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
        ]);


        try {

            $userOtp = UserOtp::where(["email" => $request->email, "otp" => $request->otp])->latest()->first();

            if($userOtp){
                $user = new User();

                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->display_name = $user->first_name . " " . $user->last_name;
                $user->user_type = "user";
                $user->username = $request->email ?? stristr($request->email, "@", true) . rand(100, 1000);
                $user->email = $request->email;
                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->status = 'active';
                $user->save();

                if ($user) {
                    $userOtp->expire_at = now();
                    $userOtp->update();

                    $user->assignRole($user->user_type);
                    $response = [];
                    $response["status"] = true;
                    $response["message"] = "Registered successfully";
                    $response["api_token"] = $user->createToken('auth_token')->plainTextToken;

                    return json_custom_response($response);
                }
            }

            return json_custom_response(['status' => false, 'message' => 'OTP not valid']);
        } catch (\Exception $e) {
            // return dd($e);
            return json_custom_response(['status' => false, 'message' => 'Please try again!']);
        }
    }
}
