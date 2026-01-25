<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ResetPasswordPostRequest;
use App\Http\Resources\AdminResource\PosOfflineMethodResource;
use App\Http\Resources\SiteResource\ShopPaginateResource;
use App\Models\User;
use App\Repositories\Interfaces\Admin\Addon\OfflineMethodInterface;
use App\Repositories\Interfaces\Admin\Addon\WalletInterface;
use App\Repositories\Interfaces\Admin\CurrencyInterface;
use App\Repositories\Interfaces\Admin\Marketing\CouponInterface;
use App\Repositories\Interfaces\Admin\SellerInterface;
use App\Repositories\Interfaces\Site\AddressInterface;
use App\Repositories\Interfaces\UserInterface;
use App\Services\ElitbuzzSmsService;
use App\Traits\SendMailTrait;
use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Sentinel;

class UserController extends Controller
{
    use SendMailTrait;
    public function changePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        if (config('app.demo_mode')):
            return response()->json([
                'error' => __('This function is disabled in demo server.')
            ]);
        endif;
        $request->validate([
            'current_password'       => 'required_if:is_password_set,==,1|min:6|max:32',
            'new_password'           => 'required|min:6|max:32|required_with:confirm_password|same:confirm_password',
            'confirm_password'       => 'required|min:6|max:32',
        ],
        [
            'current_password.required_if' => 'current password field is required'
        ]);

        try {
            $user = authUser();
            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'error' => __('New password cannot be same as current password')
                ]);
            }
            if(Hash::check($request->current_password, $user->password)){
            $user->password = bcrypt($request->new_password);
            $user->last_password_change = Carbon::now();
            $user->save();
                return response()->json([
                    'success' => __('Password Changed Successfully'),
                ]);
            }elseif ($request->is_password_set == 0){
                $user->password = bcrypt($request->new_password);
                $user->is_password_set = 1;
                $user->save();
                return response()->json([
                    'success' => __('Password Set Successfully'),
                     'data' => $user,
                ]);
            } else{
                return response()->json([
                    'error' => __('Current Password does not match with old password')
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'phone'      => [
                'required',
                'regex:/^(?:\+88|88)?01[3-9]\d{8}$/',
            ],
        ]);

        try {
            $phone = str_replace(' ', '', $request->phone);
            $phone = preg_replace('/^(?:\+88|88)/', '', $phone);
            $user = User::where('phone', $phone)->first();

            if (!$user || $user->status != 1) {
                return response()->json([
                    'error' => __('User not found.'),
                ], 404);
            }

            if ($user->otp_sent_at &&
                now()->diffInMinutes($user->otp_sent_at) < 2) {
                return response()->json([
                    'error' => __('Verification code was already sent. Please wait a few minutes.'),
                ], 429);
            }

            $otp = rand(10000, 99999);

            // Developer: Log OTP before sending SMS
            \Log::info('🔔 DEVELOPER OTP GENERATED', [
                'phone' => $phone,
                'otp' => $otp,
                'purpose' => 'password_reset',
                'valid_for_minutes' => 5
            ]);

            $smsService = new ElitbuzzSmsService();
            $sent = $smsService->resetPassword($phone, [
                'otp'  => $otp,
            ]);

            if (!$sent) {
                return response()->json([
                    'error' => __('Failed to send OTP. Please try again later.'),
                ], 500);
            }

            $user->otp = $otp;
            $user->otp_sent_at = now();
            $user->save();

            return response()->json([
                'success' => __('You have received an sms for reset your password')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getResetActivation($email, $resetCode)
    {
        $user       = User::byEmail($email);

        if ($reminder = Reminder::exists($user, $resetCode)) :
            return redirect()->route('home');
        else :
            return redirect()->route('login');
        endif;
    }

    public function createNewPassword(ResetPasswordPostRequest $request)
    {
        try {
            // Phone + OTP flow
            if ($request->filled('phone')) {
                $phone = str_replace(' ', '', $request->phone);
                $user = User::where('phone', $phone)->first();

                if (!$user || $user->status != 1) {
                    return response()->json([
                        'error' => __('User not found.'),
                    ], 404);
                }

                // Verify OTP if OTP verification is enabled
                if (function_exists('settingHelper') ? settingHelper('disable_otp_verification') != 1 : true) {
                    if (!$request->filled('otp')) {
                        return response()->json([
                            'error' => __('Invalid OTP.'),
                        ], 422);
                    }

                    if ((string)$user->otp !== (string)$request->otp) {
                        return response()->json([
                            'error' => __('OTP does not match.'),
                        ], 422);
                    }

                    if (!empty($user->otp_sent_at)) {
                        try {
                            if (now()->diffInMinutes($user->otp_sent_at) > 5) {
                                return response()->json([
                                    'error' => __('OTP has expired. Please request a new one.'),
                                ], 422);
                            }
                        } catch (\Throwable $t) {
                        }
                    }
                }

                if (!$request->filled('newPassword')) {
                    return response()->json([
                        'success' => __('OTP Verified Successfully'),
                    ]);
                }

                // Update password and clear OTP
                $user->password = bcrypt($request->newPassword);
                $user->otp = null;
                if (isset($user->otp_sent_at)) {
                    $user->otp_sent_at = null;
                }
                $user->save();

                return response()->json([
                    'success' => __('Successfully Password Changed')
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function coupons(CouponInterface $coupon): \Illuminate\Http\JsonResponse
    {

        try {
            $data = [
                'coupons' => 100,
            ];
            return response()->json($data);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function points(CouponInterface $points)
    {
        try {
            $data = [
                'points' => $points->pointPage()
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function editProfile(AddressInterface $address): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'addresses' => $address->userAddress(),
            ];
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateProfile(Request $request,UserInterface $userInterface): \Illuminate\Http\JsonResponse
    {
        if (config('app.demo_mode')):
            return response()->json([
                'error' => __('This function is disabled in demo server.')
            ]);
        endif;
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required_without:email',
            'gender' => 'required',
            'date_of_birth' => 'required',
        ]);
        try {
            if($request->phone):
                $request['phone'] = str_replace(' ','',$request->phone);
            endif;

            $userInterface->update($request);

            $data['user'] = User::find(authId());
            $data['success'] = __('Profile Updated Successfully');

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function walletData(CurrencyInterface $currency,OfflineMethodInterface $offlineMethod): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'indian_currency'   => $currency->currencyByCode('INR'),
                'offline_methods'   => addon_is_activated('offline_payment') ? PosOfflineMethodResource::collection($offlineMethod->activeMethods()) : [],
                'jazz_data'         => [],
                'jazz_url'          => config('jazz_cash.TRANSACTION_POST_URL'),
                'xof'               => $currency->currencyByCode('XOF'),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function walletRecharge(Request $request,WalletInterface $wallet)
    {
        try {
            if ($request->user_id && !authUser())
            {
                $user = User::find($request->user_id);
                Sentinel::login($user);
            }

            $data = [
                'success' => 'Recharge Successful',
                'wallet' => $wallet->walletRecharge($request->all()),
                'recharges' => $wallet->walletHistory(),
                'balance' => $wallet->userBalance()
            ];

            if ($request->token)
            {
                return redirect()->route('api.payment.success');
            }

            if (!request()->ajax())
            {
                return redirect('my-wallet');
            }
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function walletHistory(WalletInterface $wallet): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'recharges' => $wallet->walletHistory(),
                'balance' => $wallet->userBalance()
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function followShop(Request $request,SellerInterface $sellers)
    {
        try {
            $sellers->followSeller($request->id);

            $data = [
                /*'follower' => FollowShop::collection($sellers->shop()
                    ->withCount('products')
                    ->whereHas('users', function($q){
                        $q->where('user_id',authId());
                    })
                    ->where('verified_at','!=',null)
                    ->orderBy('products_count','desc')
                    ->latest()
                    ->get()),*/
                'success' => 'Added Successfully'
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
    public function userFollowedShop(SellerInterface $sellers)
    {
        try {
            $data = [
                'sellers' => new ShopPaginateResource($sellers->shop()
                    ->withCount('products')
                    ->whereHas('users', function($q){
                        $q->where('user_id',authId());
                    })
                    ->where('verified_at','!=',null)
                    ->orderBy('products_count','desc')
                    ->latest()
                    ->paginate(12))
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function removeFollow(Request $request,SellerInterface $seller)
    {
        try {
            $data = [
                'follower' => $seller->unfollowSeller($request->id),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
}
