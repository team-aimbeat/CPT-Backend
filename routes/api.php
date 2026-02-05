<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register',[ API\UserController::class, 'register']);
Route::post('assign-workout/status', [API\UserController::class, 'updateAssignmentStatusByUserAndWorkout']);


Route::get('assign-workouts/user/{user_id}/workout/month', [API\UserController::class, 'getMonthlyAssignmentStatus']);
Route::post('login',[ API\UserController::class, 'login']);
Route::post('forget-password',[ API\UserController::class, 'forgetPassword']);
Route::post('social-mail-login',[ API\UserController::class, 'socialMailLogin' ]);
Route::post('social-otp-login',[ API\UserController::class, 'socialOTPLogin' ]);
Route::get('user-detail',[ API\UserController::class, 'userDetail']);
Route::get('get-appsetting', [ API\UserController::class, 'getAppSetting'] );
Route::get('language-table-list',[API\LanguageTableController::class, 'getList']);
Route::get('language-list',[API\LanguageTableController::class, 'geLanguagetList']);


Route::post('google-auth', [API\UserController::class, 'googleLoginOrRegister']);


Route::post('user/login/mail_otp',[API\UserController::class, 'mailOtpSubmit']);
Route::post('user/login/mail_otp/check',[API\UserController::class, 'mailOtpCheck']);
Route::post('user/login/mail_otp/register',[API\UserController::class, 'registerByMailOtp']);


Route::get('equipment-list', [ API\EquipmentController::class, 'getList' ]);

Route::get('injury-list', [ API\InjuryController::class, 'getList' ]);


Route::get('level-list', [ API\LevelController::class, 'getList' ]);

 Route::get('bodypart-list', [ API\BodyPartController::class, 'getList' ]);
 
  Route::get('workouttype-list', [ API\WorkoutTypeController::class, 'getList' ]);


Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::get('dashboard-detail',[ API\DashboardController::class, 'dashboard']);
    Route::post('update-profile', [ API\UserController::class, 'updateProfile']);
    
    Route::post('update-workout-mode', [API\UserController::class, 'updateWorkoutMode']);
    
    Route::post('change-password', [ API\UserController::class, 'changePassword']);
    Route::post('update-user-status', [ API\UserController::class, 'updateUserStatus']);
    Route::post('delete-user-account', [ API\UserController::class, 'deleteUserAccount']);
    Route::get('logout',[ API\UserController::class, 'logout']);

    Route::get('payment-gateway-list', [ API\PaymentGatewayController::class, 'getList'] );
    
    Route::post('/payment-complete', [API\PaymentGatewayController::class, 'completePayment']);

    Route::get('assign-diet-list', [ API\AssignUserController::class, 'getAssignDiet' ]);
    
    //added by pooja
    Route::get('/user/{id}/assigned-diets', [API\AssignUserController::class, 'getAssignedDiet']);

    Route::get('assign-workout-list', [ API\AssignUserController::class, 'getAssignWorkout' ]);
    
    //get all exercise video
    Route::get('/exercises/video-urls', [API\ExerciseController::class, 'getVideoUrls']);
    Route::get('all-videos', [ API\EquipmentVideoController::class, 'getList' ]);

    

    Route::get('categorydiet-list', [ API\CategoryDietController::class, 'getList' ]);

   

    Route::get('diet-list', [ API\DietController::class, 'getList' ]);
    Route::get('diet-list-v2', [ API\DietController::class, 'getListV2' ]);
    Route::post('diet-detail', [ API\DietController::class, 'getDetail' ]);

    Route::get('category-list', [ API\CategoryController::class, 'getList' ]);
    Route::get('tags-list', [ API\TagsController::class, 'getList' ]);

    
    
   
    
    Route::get('workout-list', [ API\WorkoutController::class, 'getList' ]);
    Route::get('workout-detail', [ API\WorkoutController::class, 'getDetail' ]);
    Route::get('workoutday-list', [ API\WorkoutController::class, 'workoutDayList' ]);
    Route::get('workoutday-exercise-list', [ API\WorkoutController::class, 'workoutDayExerciseList' ]);
    
    Route::get('get-favourite-diet', [ API\DietController::class, 'getUserFavouriteDiet' ]);
    Route::post('set-favourite-diet', [ API\DietController::class, 'userFavouriteDiet' ]);

    Route::get('exercise-list', [ API\ExerciseController::class, 'getList' ]);
    Route::get('exercise-detail', [ API\ExerciseController::class, 'getDetail' ]);
   
    Route::get('post-list', [ API\PostController::class, 'getList' ]);
    Route::post('post-detail', [ API\PostController::class, 'getDetail' ]);

    Route::get('get-favourite-workout', [ API\WorkoutController::class, 'getUserFavouriteWorkout' ]);
    Route::post('set-favourite-workout', [ API\WorkoutController::class, 'userFavouriteWorkout' ]);
    
    //added by pooja Route to get a specific user's assigned workouts
Route::get('user/workouts', [API\WorkoutController::class, 'getUserAssignedWorkouts']);

Route::get('user/{id}/circular-workouts', [API\WorkoutController::class, 'getAbsenteeCircularWorkouts']);

Route::post('/exercise/complete', [API\WorkoutController::class, 'markExerciseComplete']);


Route::get('/attendance/monthly', [API\WorkoutController::class, 'getMonthlyAttendance']);



// In routes/api.php
Route::get('/exercise/{exerciseId}', [API\WorkoutController::class, 'getExerciseDetails']);
    
    Route::post('store-user-exercise', [ API\ExerciseController::class, 'storeUserExercise' ]);
    Route::get('get-user-exercise', [ API\ExerciseController::class, 'getUserExercise' ]);
    
    Route::get('product-list', [ API\ProductController::class, 'getlist']);
    Route::get('productcategory-list', [ API\ProductCategoryController::class, 'getlist']);
    Route::post('product-detail', [ API\ProductController::class, 'getDetail']);

    Route::get('package-list', [ API\PackageController::class, 'getList' ]);

    Route::get('subscriptionplan-list',[ API\SubscriptionController::class, 'getList']);
    Route::post('subscribe-package',[ API\SubscriptionController::class, 'subscriptionSave']);
    Route::post('cancel-subscription',[ API\SubscriptionController::class, 'cancelSubscription']);
    Route::post('apply-coupon', [ API\CouponController::class, 'apply' ]);
    Route::get('offer-coupons', [ API\CouponController::class, 'getOfferCoupons' ]);


    Route::get('get-setting',[ API\DashboardController::class, 'getSetting']);

    Route::post('usergraph-save', [ API\UserGraphController::class, 'saveGraphData']);
    Route::get('usergraph-list', [ API\UserGraphController::class, 'getGraphDataList']);
    Route::post('usergraph-delete', [ API\UserGraphController::class, 'deleteGraphData']);

    Route::post('notification-list', [ API\NotificationController::class, 'getList'] );
    Route::get('notification-detail', [ API\NotificationController::class, 'getNotificationDetail'] );

    Route::get('user-profile-detail',[ API\UserController::class, 'userProfileDetail']); 

    Route::get('chatgpt-fit-bot-list',[ API\ChatgptFitBotController::class, 'getList']); 
    Route::post('chatgpt-fit-bot-save',[ API\ChatgptFitBotController::class, 'store']); 
    Route::post('chatgpt-fit-bot-delete',[ API\ChatgptFitBotController::class, 'destroy']); 

    Route::get('class-schedule-list',[ API\ClassScheduleController::class, 'getList']); 

    Route::get('class-schedule-list',[ API\ClassScheduleController::class, 'getList']);
    Route::post('class-schedule-plan-save',[ API\ClassScheduleController::class, 'storeClassSchedulePlan']);
});
