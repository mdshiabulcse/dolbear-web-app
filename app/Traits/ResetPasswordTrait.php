<?php

namespace App\Traits;

use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Reminder;

trait ResetPasswordTrait
{

    public function resetPassword($phone, $otp)
    {
        $user = User::where('phone', $phone)->first();

        if ($user->otp !== $otp) {
            return redirect()->route('login');
        }
    }

}
