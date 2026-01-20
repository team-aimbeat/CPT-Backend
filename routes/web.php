<?php

// Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Security\RolePermission;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\PermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LanguageController;

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
// Packages
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\CategoryDietController;
use App\Http\Controllers\WorkoutTypeController;
use App\Http\Controllers\DietController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\BodyPartController;
use App\Http\Controllers\ClassScheduleController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PackageController;

use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;

use App\Http\Controllers\PushNotificationController;

use App\Http\Controllers\SubscriptionController;

use App\Http\Controllers\QuotesController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\DefaultkeywordController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\InjuryController;
use App\Http\Controllers\LanguageListController;
use App\Http\Controllers\LanguageWithKeywordListController;
use App\Http\Controllers\SubAdminController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\Web\HomeController as WebHomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__.'/auth.php';

Route::get('/storage', function () {
    Artisan::call('storage:link');
});

Route::get('optimize', function () {
    Artisan::call('optimize:clear');
     Artisan::call('route:clear');
    $outputRoute = Artisan::output();
    
    // Config Cache Clear करना
    Artisan::call('config:clear');
    $outputConfig = Artisan::output();
    
     return '<!DOCTYPE html><html><head><title>Cache Cleared</title></head><body>'
        . '<h2>✅ Route Cache Cleared Successfully</h2>'
        . '<pre>' . nl2br(e($outputRoute)) . '</pre>'
        . '<h2>✅ Config Cache Cleared Successfully</h2>'
        . '<pre>' . nl2br(e($outputConfig)) . '</pre>'
        . '</body></html>';
});

// use Illuminate\Support\Facades\Schema;
// Route::get('migrate', function(){
//     try {
//         // check user table exist or not
//         $schema = Schema::hasTable('users');
//         // Run migrations
//         Artisan::call('migrate', ['--force' => true]);

//         // if users table not exit than run seeder command
//         if( !$schema ) {
//             // Run seeders
//             Artisan::call('db:seed', ['--force' => true]);
//         }

//         return redirect()->route('dashboard');
//     } catch (\Exception $e) {
//         return 'Migration failed: ' . $e->getMessage();
//     }
// });



Route::get('/', function () {
    return redirect('login');
});

Route::get('language/{locale}', [ HomeController::class, 'changeLanguage'])->name('change.language');
Route::get('/home1', [WebHomeController::class, 'index'])->name('web.home');
Route::get('/plan-pricing', [WebHomeController::class, 'planPricing'])->name('web.plan-pricing');
Route::get('/program-list', [WebHomeController::class, 'programList'])->name('web.program-list');
Route::get('/contact', [WebHomeController::class, 'contact'])->name('web.contact');
Route::get('/about', [WebHomeController::class, 'about'])->name('web.about');
Route::get('/privacy-policy', [WebHomeController::class, 'privacyPolicy'])->name('web.privacy-policy');
Route::get('/term-condition', [WebHomeController::class, 'termCondition'])->name('web.term-condition');
Route::get('/faq', [WebHomeController::class, 'faq'])->name('web.faq');
Route::get('/blog', [WebHomeController::class, 'blog'])->name('web.blog');
Route::get('/blog/detail/{id}', [WebHomeController::class, 'blogDetail'])->name('web.blog.detail');
Route::post('user/subscriber',[WebHomeController::class, 'subscriber'])->name('web.subscriber');

Route::post('user/login',[WebHomeController::class, 'loginSubmit'])->name('web.login.submit');
Route::post('user/login/mail_otp',[WebHomeController::class, 'mailOtpSubmit'])->name('web.login.mail_otp');
Route::post('user/login/mail_otp/check',[WebHomeController::class, 'mailOtpCheck'])->name('web.login.mail_otp.check');
Route::post('user/login/mail_otp/register',[WebHomeController::class, 'registerByMailOtp'])->name('web.login.mail_otp.register');
Route::post('user/register',[WebHomeController::class, 'registerSubmit'])->name('web.login.register');
Route::post('contact/submit',[WebHomeController::class, 'contactSubmit'])->name('web.contact.submit');

Route::prefix('login')->group(function () {
    Route::prefix('/facebook')->group(function () {
      Route::get('', [WebHomeController::class, 'redirectToFacebook'])->name('web.login.facebook');
  
      Route::get('/callback', [WebHomeController::class, 'handleFacebookCallback'])->name('web.login.facebook.callback');
    });
  
    // user login via google route
    Route::prefix('/google')->group(function () {
      Route::get('', [WebHomeController::class, 'redirectToGoogle'])->name('web.login.google');
  
      Route::get('/callback', [WebHomeController::class, 'handleGoogleCallback'])->name('web.login.google.callback');
    });
    
});

Route::get('exercise/{exercise}/add-video', [ExerciseController::class, 'video'])
    ->name('exercise.add-video');
    
    Route::delete('/exercise/video/{exerciseVideo}', [ExerciseController::class, 'destroyVideo'])
    ->name('exercise.video.destroy');

Route::group(['middleware' => [ 'auth', 'useractive' ], 'prefix'=>'admin'], function () {
    // Permission Module
    Route::resource('permission', PermissionController::class);
    Route::get('permission/add/{type}',[ PermissionController::class, 'addPermission' ])->name('permission.add');
    Route::post('permission/save',[ PermissionController::class, 'savePermission' ])->name('permission.save');

	Route::resource('role', RoleController::class);

    // Dashboard Routes
    
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
	Route::get('changeStatus', [ HomeController::class, 'changeStatus'])->name('changeStatus');

    // Users Module
    Route::get('get-user-exercise', [ UserController::class, 'getUserExercise' ])->name("user.exercise.list");
    Route::resource('users', UserController::class);
    Route::resource('injury', InjuryController::class);
    
    Route::resource('equipment', EquipmentController::class);

    Route::resource('subadmin', SubAdminController::class);

    Route::get('users-graph',[ UserController::class, 'fetchUserGraph' ])->name('user.fetchGraph');
    
    //assign deit
    Route::get('assigndiet/{user_id}',[ UserController::class, 'assignDietForm' ])->name('add.assigndiet');
    Route::post('assigndiet',[ UserController::class, 'assignDietSave' ])->name('save.assigndiet');
    Route::post('assigndiet-delete',[ UserController::class, 'assignDietDestroy' ])->name('delete.assigndiet');

    Route::get('assigndiet-list',[ UserController::class, 'getAssignDietList'])->name('get.assigndietlist');
    //assign workout
    Route::get('assignworkout/{user_id}',[ UserController::class, 'assignWorkoutForm' ])->name('add.assignworkout');
    Route::post('assignworkout',[ UserController::class, 'assignWorkoutSave' ])->name('save.assignworkout');
    Route::post('assignworkout-delete',[ UserController::class, 'assignWorkoutDestroy' ])->name('delete.assignworkout');

    Route::get('assignworkout-list',[ UserController::class, 'getAssignWorkoutList'])->name('get.assignworkoutlist');

    //Fitness CategoryDiet 
    Route::resource('categorydiet', CategoryDietController::class);
    
    //Fitness Workout 
    Route::resource('workouttype', WorkoutTypeController::class);

    Route::resource('diet', DietController::class);
    Route::resource('category', CategoryController::class);
    
    //FitnessTags
    Route::resource('tags', TagsController::class);
    //Fitnessleval
    Route::resource('level', LevelController::class);
    Route::resource('subscribers', SubscriberController::class);

    Route::resource('bodypart', BodyPartController::class);

    Route::resource('exercise', ExerciseController::class);
    
    // Route for storing the new video/language entry
Route::post('/exercise/store-video', [ExerciseController::class, 'storeVideo'])
    ->name('exercise.store_video');
    
    //added by pooja
//     Route::get('/exercise/video', function () {
//     return view('exercise.video');
// })->name('exercise.video');




    Route::get('workout/list', [WorkoutController::class, 'getAjaxList'])->name('workout.list.paginate');
    Route::resource('workout', WorkoutController::class);

    Route::post('workoutdays-exercise-delete', [ WorkoutController::class , 'workoutDaysExerciseDelete'])->name('workoutdays.exercise.delete');

    Route::resource('post', PostController::class);
    
    //product
    Route::resource('product',ProductController::class);
    Route::resource('productcategory',ProductCategoryController::class);

    Route::resource('packages',PackageController::class);
    

    Route::post('remove-file',[ HomeController::class, 'removeFile' ])->name('remove.file');
    
    Route::get('setting/{page?}', [ SettingController::class, 'settings'])->name('setting.index');
    Route::post('layout-page', [ SettingController::class, 'layoutPage'])->name('layout_page');
    Route::post('settings/save', [ SettingController::class , 'settingsUpdates'])->name('settingsUpdates');
    Route::post('mobile-config-save',[ SettingController::class , 'settingUpdate'])->name('settingUpdate');
	Route::post('env-setting', [ SettingController::class , 'envChanges'])->name('envSetting');
    Route::post('payment-settings/save',[ SettingController::class , 'paymentSettingsUpdate'])->name('paymentSettingsUpdate');
    Route::post('subscription-settings/save',[ SettingController::class , 'subscriptionSettingsUpdate'])->name('subscriptionSettingsUpdate');

    Route::post('get-lang-file', [ LanguageController::class, 'getFile' ] )->name('getLanguageFile');
    Route::post('save-lang-file', [ LanguageController::class, 'saveFileContent' ] )->name('saveLangContent');

    Route::post('update-profile', [ SettingController::class , 'updateProfile'])->name('updateProfile');
    Route::post('change-password', [ SettingController::class , 'changePassword'])->name('changePassword');

    Route::get('pages/term-condition',[ SettingController::class, 'termAndCondition'])->name('pages.term_condition');
    Route::post('term-condition-save',[ SettingController::class, 'saveTermAndCondition'])->name('pages.term_condition_save');

    Route::get('pages/privacy-policy',[ SettingController::class, 'privacyPolicy'])->name('pages.privacy_policy');
    Route::post('privacy-policy-save',[ SettingController::class, 'savePrivacyPolicy'])->name('pages.privacy_policy_save');

    Route::resource('pushnotification', PushNotificationController::class);

    Route::resource('subscription', SubscriptionController::class);

    Route::resource('quotes', QuotesController::class);

    Route::resource('classschedule', ClassScheduleController::class);

    // Language Setting Route 
    Route::resource('screen', ScreenController::class);
    Route::resource('defaultkeyword', DefaultkeywordController::class);
    Route::resource('languagelist', LanguageListController::class);
    Route::resource('languagewithkeyword', LanguageWithKeywordListController::class);
    Route::get('download-language-with-keyword-list', [ LanguageWithKeywordListController::class, 'downloadLanguageWithKeywordList'])->name('download.language.with,keyword.list');

    Route::post('import-language-keyword', [ LanguageWithKeywordListController::class,'importlanguagewithkeyword' ])->name('import.languagewithkeyword');
    Route::get('bulklanguagedata', [ LanguageWithKeywordListController::class,'bulklanguagedata' ])->name('bulk.language.data');
    Route::get('help', [ LanguageWithKeywordListController::class,'help' ])->name('help');
    Route::get('download-template', [ LanguageWithKeywordListController::class,'downloadtemplate' ])->name('download.template');


    Route::resource('faqs',FaqController::class);
});

Route::get('/ajax-list',[ HomeController::class, 'getAjaxList' ])->name('ajax-list');


//Auth pages Routs
Route::group(['prefix' => 'auth'], function() {
    Route::get('signin', [HomeController::class, 'signin'])->name('auth.signin');
    Route::get('signup', [HomeController::class, 'signup'])->name('auth.signup');
    Route::get('confirmmail', [HomeController::class, 'confirmmail'])->name('auth.confirmmail');
    Route::get('lockscreen', [HomeController::class, 'lockscreen'])->name('auth.lockscreen');
    Route::get('recover-password', [HomeController::class, 'recoverpw'])->name('auth.recover-password');
    Route::get('userprivacysetting', [HomeController::class, 'userprivacysetting'])->name('auth.userprivacysetting');
});

//Error Page Route
Route::group(['prefix' => 'errors'], function() {
    Route::get('error404', [HomeController::class, 'error404'])->name('errors.error404');
    Route::get('error500', [HomeController::class, 'error500'])->name('errors.error500');
    Route::get('maintenance', [HomeController::class, 'maintenance'])->name('errors.maintenance');
});



//Extra Page Routs
// Route::get('privacy-policy',[ HomeController::class, 'privacyPolicy' ])->name('privacyPolicy');
// Route::get('terms-condition',[ HomeController::class, 'termsCondition' ])->name('termsCondition');