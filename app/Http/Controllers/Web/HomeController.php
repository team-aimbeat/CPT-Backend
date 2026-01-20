<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $basicPackageList = Package::where(['status' => 'active', 'package_type' => 'basic_workout'])->get();

        return view("frontend.index", compact("basicPackageList"));
    }

    public function planPricing(Request $request)
    {
        $basicPackageList = Package::where(['status' => 'active', 'package_type' => 'basic_workout'])->get();
        $workoutPackageList = Package::where(['status' => 'active', 'package_type' => 'workout'])->get();
        return view("frontend.plan-pricing", compact("basicPackageList", 'workoutPackageList'));
    }

    public function programList(Request $request)
    {
        return view("frontend.program-list");
    }

    public function contact(Request $request)
    {
        return view("frontend.contact");
    }

    public function about(Request $request)
    {
        return view("frontend.about");
    }

    public function privacyPolicy(Request $request)
    {
        $data = SettingData('privacy_policy', 'privacy_policy');
        return view("frontend.privacy-policy", compact('data'));
    }

    public function termCondition(Request $request)
    {
        $data = SettingData('terms_condition', 'terms_condition');
        return view("frontend.term-condition", compact('data'));
    }

    public function faq(Request $request)
    {
        $faqs = Faq::where("status", 'active')->get();
        return view("frontend.faq", compact('faqs'));
    }

    public function blog(Request $request)
    {
        $postList = Post::orderBy('created_at', 'desc')->paginate(6);

        return view("frontend.blog-list", compact('postList'));
    }

    public function blogDetail($id, Request $request)
    {
        $data = Post::find($id);
        $tagsList = Tags::whereIn('id', $data->tags_id)->get();
        $categoryList = Category::whereIn('id', $data->category_ids)->get();

        $recentPostList = Post::orderBy('created_at', 'desc')->limit(4)->get();
        $featuredPostList = Post::where(['is_featured' => 'yes'])->orderBy('created_at', 'desc')->limit(4)->get();

        return view("frontend.blog-detail", compact('data', 'tagsList', 'categoryList', 'recentPostList', 'featuredPostList'));
    }

    public function loginSubmit(Request $request)
    {

        $data = $request->all();
        $user = User::where(['email' => $data['email'], 'status' => 'active'])->first();

        if ($user && Hash::check($data['password'], $user->password)) {
            Auth::login($user);
            $response = [];
            $response["status"] = true;
            $response["message"] = "Login successfully";

            return response()->json($response);
        } else {
            $response = [];
            $response["status"] = false;
            $response["message"] = "Username and password not match";

            return response()->json($response);
        }
    }

    public function registerSubmit(Request $request)
    {

        $request->validate([
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'phone_number' => 'string|required|min:10',
            'email' => 'string|required|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);


        $input = $request->all();

        $password = $input['password'];
        $input['user_type'] = 'user';
        $input['password'] = Hash::make($password);
        $input['username'] = $request->username ?? stristr($request->email, "@", true) . rand(100, 1000);
        $input['status'] = 'active';

        $input['display_name'] = $input['first_name'] . " " . $input['last_name'];
        $user = User::create($input);
        $user->assignRole($input['user_type']);

        if ($user) {
            Auth::login($user);
            return response()
                ->json(['status' => true, 'message' => 'Registered successfully']);
        } else {
            return response()
                ->json(['status' => false, 'message' => 'Please try again!']);
        }
    }

    public function contactSubmit(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);


        try {
            $messageText = "New Contact Received\n\n";
            $messageText .= "Full Name: " . $request->name . "\n";
            $messageText .= "Email: " . $request->email . "\n";
            $messageText .= "Subject: " . $request->subject . "\n";
            $messageText .= "Message: " . $request->message . "\n";

            Mail::raw($messageText, function ($message) {
                $message->to(appSettingData('get')->contact_email)
                    ->subject('New Contact Received');
            });
        } catch (\Exception $e) {
            return response()
                ->json(['status' => false, 'message' => 'Please try again!']);
            // return dd($e);
        }

        return response()
            ->json(['status' => true, 'message' => 'Registered successfully']);
    }



    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback(Request $request)
    {
        if ($request->has('error_code')) {
            Session::flash('error', $request->error_message);
            return redirect()->route('user.login');
        }
        return $this->authenticationViaProvider('facebook');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        return $this->authenticationViaProvider('google');
    }


    public function authenticationViaProvider($driver)
    {
        $redirectURL = route('dashboard');

        $responseData = Socialite::driver($driver)->user();
        $userInfo = $responseData->user;

        $isUser = User::query()->where('email', '=', $userInfo['email'])->first();

        if (!empty($isUser)) {
            // log in
            if ($isUser->status == 'active') {
                Auth::guard('web')->login($isUser);

                return redirect($redirectURL);
            } else {
                Session::flash('error', 'Sorry, your account has been deactivated.');

                return redirect()->route('user.login');
            }
        } else {

            // sign up
            $user = new User();

            if ($driver == 'facebook') {
                $user->first_name = $userInfo['name'];
            } else {
                $user->first_name = $userInfo['given_name'];
            }
            
            $user->display_name = $user->first_name;
            $user->user_type = "user";
            $user->username = $userInfo['email'] ?? stristr($userInfo['email'], "@", true) . rand(100, 1000);
            $user->email = $userInfo['email'];
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->status = 'active';
            $user->provider = ($driver == 'facebook') ? 'facebook' : 'google';
            $user->provider_id = $userInfo['id'];
            $user->save();

            if ($user) {
                $user->assignRole($user->user_type);
                Auth::guard('web')->login($user);
            }


            return redirect($redirectURL);
        }
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
                    "otp" => random_int(000001, 999999),
                    "expire_at" => $now->addMinutes(10)
                ]);
            }

            $messageText = "\n\n";
            $messageText .= "OTP is " . $userOtp->otp . " for your verification on " . env('APP_NAME') . ". This OTP can be used only once and is valid for 10 min only\n";

            Mail::raw($messageText, function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Login Otp');
            });

            return response()
            ->json(['status' => true, 'message' => 'OTP Mail sent successfully', 'a'=>$userOtp->otp]);
        } catch (\Exception $e) {
            // return dd($e);
            return response()
                ->json(['status' => false, 'message' => 'Please try again!']);
        }
        
    }

    public function mailOtpCheck(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'otp' => 'required|min:6',
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

            $redirectURL = route('dashboard');
            if ($isUser) {
                if($isUser->status == 'active'){
                    $userOtp->expire_at = now();
                    $userOtp->update();

                    Auth::guard('web')->login($isUser);
                    $response["status"] = true;
                    $response["url"] = $redirectURL;
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
                return response()->json($response);
            }
        } catch (\Exception $e) {
            return response()
                ->json(['status' => false, 'message' => 'Please try again!']);
            // return dd($e);
        }
        return response()
            ->json(['status' => false, 'message' => 'Something wrong']);
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
                    Auth::guard('web')->login($user);
                    return response()
                        ->json(['status' => true, 'message' => 'Registered successfully']);
                }
            }
            return response()
            ->json(['status' => false, 'message' => 'OTP not valid']);
        } catch (\Exception $e) {
            // return dd($e);
            return response()
                ->json(['status' => false, 'message' => 'Please try again!']);
        }
        return response()
            ->json(['status' => false, 'message' => 'Please try again!']);
    }

    public function subscriber(Request $request)
    {

        $request->validate([
            'email' => 'required'
        ]);

        $subscriber = Subscriber::where("email", $request->email)->first();
        if(!$subscriber){
            Subscriber::create([
                "email"=>$request->email
            ]);
        }
        return response()
            ->json(['status' => true, 'message' => 'Subscribed successfully']);
    }
}
