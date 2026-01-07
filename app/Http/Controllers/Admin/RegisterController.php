<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\CustomerSyncJob;
use App\Models\DeliveryAddress;
use App\Repositories\Erp\CustomerRepository;
use App\Services\ElitbuzzSmsService;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\SendMailTrait;
use App\Traits\SmsSenderTrait;
use App\Traits\SendNotification;
use App\Utility\AppSettingUtility;
use Illuminate\Support\Facades\DB;
use App\Models\RegistrationRequest;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\User\SignUpRequest;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use App\Repositories\Interfaces\Admin\SellerProfileInterface;
use Sentinel;

class RegisterController extends Controller
{
    use SmsSenderTrait,SendMailTrait,SendNotification;

    public function register()
    {

        return view('admin.authenticate.register');
    }

    public function postRegister(SignUpRequest $request)
    {
        DB::beginTransaction();
        try {

            if ($request->phone) {
                $request['phone'] = str_replace(' ','',$request->phone);

                if (settingHelper('disable_otp_verification') != 1)
                {
                    $req = User::where('phone',$request->phone)->first();
                    if (!$req)
                    {
                        return response()->json([
                            'error' => __('User Not Found'),
                        ]);
                    }

                    if ($request->otp != $req->otp) {
                        return response()->json([
                            'error' => __('OTP Doesnt Match')
                        ]);
                    }

                    if (!empty($req->otp_sent_at)) {
                        try {
                            if (now()->diffInMinutes($req->otp_sent_at) > 5) {
                                return response()->json([
                                    'error' => __('OTP has expired. Please request a new one.'),
                                ], 422);
                            }
                        } catch (\Throwable $t) {
                        }
                    }
                }
            }

            $req->status = 1;
            $req->save();

            $user = User::where('phone',$request->phone)->first();

            // Check if activation exists
            $activation = Activation::exists($user);

            // If no activation exists, create one
            if (!$activation) {
                $activation = Activation::create($user);
            }

            // Complete the activation (marks user as active)
            Activation::complete($user, $activation->code);

            Sentinel::login($user);

            if ($request->phone) {

                $delivery_address = [
                    'user_id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'phone_no' => $user->phone,
                    'email' => ''
                ];

                DeliveryAddress::create($delivery_address);
            }

            DB::commit();

            return response()->json([
                'success' => $request->email && settingHelper('disable_email_confirmation') != 1 ?  __('Check your sms to verify your account') : __('Registration Successfully'),
                'migrate_msg' => __('Request sent successfully. Wait for approval.'),
                'user' => $user,
                'auth_user' => authUser(),
                'type' => $request->email ? 0 : 1,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function registerByPhone(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => [
                'required',
                'regex:/^(?:\+88|88)?01[3-9]\d{8}$/',
            ],
            'password'   => 'required|confirmed|min:6',
        ], [
            'phone.regex' => 'Please enter a valid Bangladeshi phone number.',
        ]);


        try {
            $phone = str_replace(' ', '', $request->phone);
            $phone = preg_replace('/^(?:\+88|88)/', '', $phone);

            // Check resend cooldown (2 minutes)
            $existingUser = User::where('phone', $phone)->first();
            if ($existingUser) {
                // If user exists but unverified and OTP sent recently
                if ($existingUser->status == 0 && $existingUser->otp_sent_at &&
                    now()->diffInMinutes($existingUser->otp_sent_at) < 2) {
                    return response()->json([
                        'error' => __('Verification code was already sent. Please wait a few minutes.'),
                    ], 429);
                }

                // If verified already
                if ($existingUser->status == 1) {
                    return response()->json([
                        'error' => __('This phone number is already registered.'),
                    ], 400);
                }
            }

            // Generate OTP
            $otp = rand(10000, 99999);

            // Send OTP
            $smsService = new ElitbuzzSmsService();
            $sent = $smsService->registration($phone, ['otp' => $otp]);


            if (!$sent) {
                return response()->json([
                    'error' => __('Failed to send OTP. Please try again later.'),
                ], 500);
            }

            // Create or update user with status = 0
            User::updateOrCreate(
                ['phone' => $phone],
                [
                    'first_name'   => $request->first_name,
                    'last_name'    => $request->last_name,
                    'password'     => bcrypt($request->password),
                    'otp'          => $otp,
                    'status'       => 0,
                    'otp_sent_at'  => now(),
                ]
            );

            return response()->json([
                'data' => __('OTP sent successfully. Please verify to activate your account.'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
