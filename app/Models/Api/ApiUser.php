<?php

/* Folder Location */

namespace App\Models\Api;

/* Dependencies */

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApiUser.
 */
class ApiUser extends User {
    public static function loginUser($id,$token,$message = '') {

        /* Find User For Provided Email */
        $model = Self::where(['id' => $id])->first();

        if ($model->verified == Self::STATUS_INACTIVE) {
            return Self::generateErrorMessage(false, 400, 'Account not verified. Please verify your account through the verification email sent to your email id.');
        }

        return [
            'status' => true,
            'data' => [
                'message' => ($message == '') ? 'User Login successfully.' : $message,
                'user' => $model->getArrayResponse(),
                'token' => $token
            ]
        ];
    }
    
    public function getArrayResponse() {
        return [
            'id'            =>  $this->id,
            'title'         =>  $this->getTitle(),
            'first_name'    =>  $this->getFirstName(),
            'last_name'     =>  $this->getLastName(),
            'fullname'      => $this->fullname,
            'username'      => $this->username,
            'verified'      => $this->statusVerified(),
            'roles' => [
                'id'    => $this->roleId,    
                'label' => $this->role->label,
            ]
            // 'profile_picture' => (!empty($this->avatar_location) && file_exists(public_path($this->avatar_location))) ? url('public/'.$this->avatar_location) : ''
        ];
    }

    public function sendEmailForgotPassword($token,$firstName){
        \Mail::send('vendor.mail.html.waves.forgotPassword', [
                'token' => $token,
                'firstName' => $firstName,
                'userId' => $this->id,
            ], function ($message) {
                $message->from('hello@abovethewaves.co', 'The Waves Team');
                $message->to($this->username)->subject('Password Reset');
        });

        return true;
    }
}