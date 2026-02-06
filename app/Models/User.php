<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'username', 'first_name', 'last_name', 'phone_number', 'status', 'email', 'password', 'gender', 'display_name', 'login_type', 'user_type', 'player_id', 'is_subscribe', 'has_coupon_access', 'coupon_access_ends_at', 'referral_credit_balance', 'timezone','last_notification_seen'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_subscribe'  => 'integer',
        'has_coupon_access' => 'boolean',
        'coupon_access_ends_at' => 'datetime',
        'referral_credit_balance' => 'decimal:2',
    ];

    public function hasActiveCouponAccess(): bool
    {
        if (!$this->has_coupon_access) {
            return false;
        }

        if (empty($this->coupon_access_ends_at)) {
            return true;
        }

        return now()->lt($this->coupon_access_ends_at);
    }

    public function userProfile() {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    public function userGraph(){
        return $this->hasMany(UserGraph::class, 'user_id', 'id');
    }

    public function userAssignDiet(){
        return $this->hasMany(AssignDiet::class, 'user_id', 'id');
    }

    public function userAssignWorkout(){
        return $this->hasMany(AssignWorkout::class, 'user_id', 'id');
    }

    public function userFavouriteDiet(){
        return $this->hasMany(UserFavouriteDiet::class, 'user_id', 'id');
    }

    public function userFavouriteWorkout(){
        return $this->hasMany(UserFavouriteWorkout::class, 'user_id', 'id');
    }

    public function userNotification(){
        return $this->hasMany(Notification::class, 'notifiable_id', 'id');
    }

    public function chatgptFitBot(){
        return $this->hasMany(ChatgptFitBot::class, 'user_id', 'id');
    }
    
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($row) {
            switch ($row->user_type) {
                case 'user':
                    $row->userProfile()->delete();
                    $row->userGraph()->delete();
                    $row->userAssignDiet()->delete();
                    $row->userAssignWorkout()->delete();
                    $row->userFavouriteDiet()->delete();
                    $row->userFavouriteWorkout()->delete();
                    $row->userNotification()->delete();
                    $row->chatgptFitBot()->delete();
                break;
                default:
                    # code...
                break;
            }
        });

        static::updated(function($model) {
            if ($model->isDirty('first_name') || $model->isDirty('last_name') ) {
                $model->display_name = $model->first_name.' '.$model->last_name;
                $model->saveQuietly(); 
            }
        });
    }

    public function routeNotificationForOneSignal()
    {
        return $this->player_id;
    }

    public function subscriptionPackage(){
        return $this->hasOne(Subscription::class, 'user_id','id')->where('status',config('constant.SUBSCRIPTION_STATUS.ACTIVE'));
    }

    public function classSchedulePlan(){
        return $this->hasMany(ClassSchedulePlan::class, 'user_id', 'id');
    }
    
    public function assignedWorkouts(): BelongsToMany
    {
        return $this->belongsToMany(
            Workout::class,
            'assign_workouts', 
            'user_id',         
            'workout_id'       
        )
        ->withTimestamps();
    }
    
    
    public function assignedDiets()
    {
        return $this->belongsToMany(Diet::class, 'assign_diets', 'user_id', 'diet_id');
    }
}
