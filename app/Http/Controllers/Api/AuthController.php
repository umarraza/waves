<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

//// JWT ////
use JWTAuthException;
use JWTAuth;

//// Models ////
use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdminProfiles;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiDeviceToken as DeviceToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // inializing a default response in case of something goes wrong.
        $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Invalid credentials or missing parameters',
                ],
                'status' => false
            ];

        // checking if parameters are set or not
        if(isset($request['username'],$request['password'])){

            // authenticate token from username and passwrod
            $credentials = $request->only('username', 'password');
            $token = null;
            try {
               if (!$token = JWTAuth::attempt($credentials)) {
                    return [
                        'data' => [
                            'code' => 400,
                            'message' => 'Email or password wrong.',
                        ],
                        'status' => false
                    ];
               }
            } catch (JWTAuthException $e) {
                return [
                        'data' => [
                            'code' => 500,
                            'message' => 'Fail to create token.',
                        ],
                        'status' => false
                    ];
            }

            // Finding User from token.
            $user = JWTAuth::toUser($token);

            // Checking if user is valid or not.
            if($user->isValidUser())
            {
                // setting code for response to 200.
                $response['data']['code'] = 200;

                // Setting a flag to check first time login for Both Admins.
                $flag = false;
                if(($user->isAdmin() || $user->isSecondaryAdmin()) && !$user->isVerified()){
                    $flag = true;
                    // verifing the user because they did'nt need signup
                    $user->verified = User::STATUS_ACTIVE; 
                    $user->save();
                }         
                $response['status'] = true; 
                $code = $response['data']['code'];      
                $response['data'] = User::loginUser($user->id,$token);
                $response['data']['code'] = $code; 

                $response['data']['data']['user'] = base64_encode(json_encode($response['data']['data']['user']));

                if($user->isAdmin()){
                    if($flag) // Changing the code from 200 to 300 for first time login.
                        $response['data']['code'] = 300;

                    // storing admin and school info in response
                    $response['data']['school'] = base64_encode(json_encode($user->schoolAdminProfile->getResponseData()));
                    // $response['data']['user'] = base64_encode(json_encode("Login"));
                }
                elseif($user->isSecondaryAdmin()){
                    if($flag) // Changing the code from 200 to 300 for first time login.
                        $response['data']['code'] = 300;
                    
                    // storing admin and school info in response
                    $response['data']['school'] = base64_encode(json_encode($user->schoolSecondaryAdminProfile->getResponseData()));
                    //$response['data']['user'] = base64_encode(json_encode("Login"));
                }elseif($user->isResponder() && $user->isVerified()){

                    // Checking if User is already login in to other devices or not
                    $loginFlag = false;
                    // if($request->deviceType=="M")
                    // {
                    //     if($user->deviceToken!=null)
                    //     {
                    //         $loginFlag = true;
                    //     }
                    // }    
                    if($loginFlag)
                    {
                        $response = [
                                        'data' => [
                                            'code' => 420,
                                            'message' => 'Already Login to the other device. Log out first',
                                        ],
                                        'status' => false
                                    ];
                    }
                    else
                    {
                        // storing responder and school info in response
                        $response['data']['user'] = base64_encode(json_encode($user->responder->getResponseData()));
                        $response['data']['school'] = base64_encode(json_encode($user->responder->schoolProfile->schoolAdminProfile->getResponseData()));
                    }
                }
                elseif($user->isStudent() && $user->isVerified()){

                    // Checking if User is already login in to other devices or not
                    $loginFlag = false;
                    // if($request->deviceType=="M")
                    // {
                    //     if($user->deviceToken!=null)
                    //     {
                    //         $loginFlag = true;
                    //     }
                    // }    
                    if($loginFlag)
                    {
                        $response = [
                                        'data' => [
                                            'code' => 420,
                                            'message' => 'Already Login to the other device. Log out first',
                                        ],
                                        'status' => false
                                    ];
                    }
                    else
                    {
                        // storing student and school info in response
                        $response['data']['user'] = base64_encode(json_encode($user->student->getResponseData()));
                        $response['data']['school'] = base64_encode(json_encode($user->student->schoolProfile->schoolAdminProfile->getResponseData()));
                    }
                }
            }
            else
            {   
                // response if user is not valid.
                $response['data']['message'] = 'Not a valid user';
            }
        }
        return $response;
    }

    public function isValidToken(Request $request){
        // validating token if mobile device is already logged in.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified()){
            $response['status'] = true;
            $response['data']['code'] = 200;
            $flag = false;
            if(($user->isAdmin() || $user->isSecondaryAdmin()) && !$user->isVerified()){
                $flag = true;
                $user->verified = User::STATUS_ACTIVE;
                $user->save();
            }      
            $token = $request->token;
            $code = $response['data']['code'];  
            $response['data'] = User::loginUser($user->id,$token,'Valid Token');
            $response['data']['code'] = $code; 
            if($user->isAdmin()){
                if($flag)
                    $response['data']['code'] = 300;

                $response['data']['school'] = base64_encode(json_encode($user->schoolAdminProfile->getResponseData()));
                //$response['data']['user'] = base64_encode(json_encode("Login"));
            }
            elseif($user->isSecondaryAdmin()){
                if($flag)
                    $response['data']['code'] = 300;
                //$response['data']['user'] = $user->schoolSecondaryAdminProfile->getResponseData();
                $response['data']['school'] = base64_encode(json_encode($user->schoolSecondaryAdminProfile->getResponseData()));
                //$response['data']['user'] = base64_encode(json_encode("Login"));
            }elseif($user->isResponder() && !$user->isUserDeleted())
            {
                // Checking if User is already login in to other devices or not
                $loginFlag = false;
                // if($request->deviceType=="M")
                // {
                //     if($user->deviceToken!=null)
                //     {
                //         $loginFlag = true;
                //     }
                // }    
                if($loginFlag)
                {
                    $response = [
                                    'data' => [
                                        'code' => 420,
                                        'message' => 'Already Login to the other device. Log out first',
                                    ],
                                    'status' => false
                                ];
                }
                else
                {
                    $response['data']['user'] = base64_encode(json_encode($user->responder->getResponseData()));
                    $response['data']['school'] = base64_encode(json_encode($user->responder->schoolProfile->schoolAdminProfile->getResponseData()));
                }
            }
            elseif($user->isStudent() && !$user->isUserDeleted())
            {
                // Checking if User is already login in to other devices or not
                $loginFlag = false;
                // if($request->deviceType=="M")
                // {
                //     if($user->deviceToken!=null)
                //     {
                //         $loginFlag = true;
                //     }
                // }    
                if($loginFlag)
                {
                    $response = [
                                    'data' => [
                                        'code' => 420,
                                        'message' => 'Already Login to the other device. Log out first',
                                    ],
                                    'status' => false
                                ];
                }
                else
                {
                    $response['data']['user'] = base64_encode(json_encode($user->student->getResponseData()));
                    $response['data']['school'] = base64_encode(json_encode($user->student->schoolProfile->schoolAdminProfile->getResponseData()));
                }
            }
        }
        return $response;
    }

    public function logout(Request $request){

        // validation user from token.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified()){
            // if user is valid then expire its token.
            JWTAuth::invalidate($request->token);
            
            // if logout occur from mobile device then clear the device token.
            // if($request->deviceType=="M")
            //     $user->clearDeviceToken();
            if($request->deviceType=="M")
            {
                $deviceToken = DeviceToken::where('deviceToken','=',$request->tokenValue)->delete();
            }

            $response['data']['message'] = 'Logout successfully.';
            $response['data']['code'] = 200;
            $response['status'] = true;
        }
        return $response;
    }

    // public function updateUserDevice(Request $request){
    //     // validation token.
    //     $user = JWTAuth::toUser($request->token);
    //     // generating default response if something gets wrong.
    //     $response = [
    //             'data' => [
    //                 'code' => 400,
    //                 'message' => 'Invalid Token! User Not Found.',
    //             ],
    //             'status' => false
    //         ];
    //     if(!empty($user) && $user->statusVerified()){
    //         // rules to check weather paramertes comes or not.
    //         $rules = [
    //             'deviceToken' => 'required',
    //             'deviceType' => 'required|Numeric|in:0,1'
    //         ];
    //         // validating rules
    //         $validator = Validator::make($request->all(), $rules);
    //         if ($validator->fails()) {
    //             $response['data']['message'] = 'Invalid input values.';
    //             $response['data']['errors'] = $validator->messages();
    //         }else
    //         {
    //             // saving success response.
    //             $response['status'] = true;
    //             $response['data']['code'] = 200;
    //             $response['data']['message'] = 'Request Successfull.';
    //             $model = User::where('id','=',$user->id)->first();
    //             // updating token
    //             if(!empty($model)){
    //                 $model->update([
    //                     'deviceToken' => $request->deviceToken,
    //                     'deviceType' => $request->deviceType
    //                 ]);
    //             }
    //             else
    //             {
    //                 // in cans token update is unsuccessfull.
    //                 $response['data']['message'] = 'Device token not saved successfully. Please try again.';
    //             }
    //         }
    //     }
    //     return $response;
    // }
    public function updateUserDevice(Request $request){
        // validation token.
        $user = JWTAuth::toUser($request->token);
        // generating default response if something gets wrong.
        $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified()){
            // rules to check weather paramertes comes or not.
            $rules = [
                'deviceToken' => 'required',
                'deviceType' => 'required|Numeric|in:0,1'
            ];
            // validating rules
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                // saving success response.
                $response['status'] = true;
                $response['data']['code'] = 200;
                $response['data']['message'] = 'Request Successfull.';

                $checkToken  = DeviceToken::where('deviceToken','=',$request->deviceToken)->first();
                if(empty($checkToken))
                {
                    $model = DeviceToken::create([
                            'deviceToken' => $request->deviceToken,
                            'deviceType' => $request->deviceType,
                            'userId'    =>  $user->id
                    ]);
                    // updating token
                    if($model){
                    }
                    else
                    {
                        // in cans token update is unsuccessfull.
                        $response['data']['message'] = 'Device token not saved successfully. Please try again.';
                    }
                }
            }
        }
        return $response;
    }

    public function getRoles(Request $request)
    {
        // generating default response if something gets wrong.
        $response = [
                'data' => [
                    'error' => 400,
                    'message' => 'No data found.',
                ],
                'status' => false
            ];
        // Get all roles with the help of Roles model.
        $modelRoles = Roles::get();

        $response['data']['message'] = 'Request Successfull';
        $response['data']['error'] = 200;
        $response['data']['result'] = $modelRoles;
        $response['status'] = true;
        return $response;
    }

    public function createAdmin(Request $request)
    {
        $response = [
            'data' => [
                'error' => 400,
                'message' => 'Something went wrong. Please try again later!',
            ],
            'status' => false
        ];
        $request['roleId'] = 'super_admin';
        $rules = [
            'username'   => ['required', 'email', 'max:191', Rule::unique('users')],
            'password'   => 'required|min:5',
            'roleId'     => ['required','exists:roles,label'],
            'code'       => ['required','in:stackcru']
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else{
            $rolesId = Roles::findByAttr('label',$request->get('roleId'))->id;
            $user = User::create([
                'username'  => $request->get('username'),
                'password'  => bcrypt($request->get('password')),
                'roleId'    => $rolesId,
                'verified'  => User::STATUS_ACTIVE
            ]);
            if ($user) {
                $response['data']['message'] = 'Request Successfull';
                $response['data']['error'] = 200;
                $response['data']['result'] = $user->getArrayResponse();
                $response['status'] = true;
            }
        }
        return $response;
    }

    // Responder & Student Signup
    public function signup(Request $request)
    {
        $response = [
            'data' => [
                'code' => 400,
                'message' => 'Something went wrong. Please try again later!',
            ],
           'status' => false
        ];
        $rules = [
            'schoolId'          => ['required', 'exists:school_admin_profiles,id'],
            'accessCode'        => ['required', 'exists:school_profiles,accessCode'],
            'role'              => ['required', 'in:'.implode(",",array_keys(User::getTypes()))],
            // 'authorizationCode' => ['required_if:role,"'.User::USER_RESPONDER, 'exists:responder_profiles,authorizationCode'],
            'resAuthCode' => ['required_if:role,"'.User::USER_RESPONDER, 'exists:responder_profiles,authorizationCode'],
            'stdAuthCode' => ['required_if:role,"'.User::USER_STUDENT, 'exists:student_profiles,authorizationCode']
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else
        {
            $response['status'] = true;
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['code'] = 500;
            if($request->get('role') == User::USER_RESPONDER){

                $modelUser = User::join('responder_profiles', 'responder_profiles.userId', '=', 'users.id')
                            ->where('responder_profiles.authorizationCode','=',$request->get('resAuthCode'))
                            ->first();
                if($modelUser->verified==0 && $modelUser->isDeleted==0)
                {
                    $modelSchoolAdminProfile = SchoolAdminProfiles::join('school_profiles', 'school_profiles.id', '=', 'school_admin_profiles.schoolProfileId')
                                ->where('school_admin_profiles.id','=',$request->get('schoolId'))->first();
                    if(!empty($modelUser) && !empty($modelSchoolAdminProfile) && $modelUser->isResponder()){

                        if($modelUser->authorizationCode == $request->get('resAuthCode') && $modelSchoolAdminProfile->accessCode == $request->get('accessCode') && $modelSchoolAdminProfile->schoolProfileId == $modelUser->schoolProfileId){
                            $resetToken = md5(time() . $modelUser->id . 'waves');

                            $user = User::where('id','=',$modelUser->userId)->first();
                            if($user){
                                $user->update([
                                    'resetPasswordToken' => $resetToken,
                                    'createdResetPToken' => date('Y-m-d H:i:s')
                                ]);
                                $response['data']['message'] = 'Request Successfull';
                                $response['data']['result'] = $user->responder->getResponseData();
                                $response['data']['resetToken'] = $resetToken;
                                $response['data']['code'] = 200;
                            }
                        }
                    }
                }
                else
                {
                    $response['data']['message'] = 'You have already signup.';
                    //$response['data']['result'] = $user->responder->getResponseData();
                    //$response['data']['resetToken'] = $resetToken;
                    $response['data']['code'] = 401;
                }
            }
            elseif($request->get('role') == User::USER_STUDENT){
                $modelUser = User::join('student_profiles', 'student_profiles.userId', '=', 'users.id')
                            ->where('student_profiles.authorizationCode','=',$request->get('stdAuthCode'))
                            ->first();
                if($modelUser->verified==0)
                {
                    $modelSchoolAdminProfile = SchoolAdminProfiles::join('school_profiles', 'school_profiles.id', '=', 'school_admin_profiles.schoolProfileId')
                                ->where('school_admin_profiles.id','=',$request->get('schoolId'))->first();
                    if(!empty($modelUser) && !empty($modelSchoolAdminProfile) && $modelUser->isStudent()){
                        if($modelUser->authorizationCode == $request->get('stdAuthCode') && $modelSchoolAdminProfile->accessCode == $request->get('accessCode') && $modelSchoolAdminProfile->schoolProfileId == $modelUser->schoolProfileId){
                            $resetToken = md5(time() . $modelUser->id . 'waves');

                            $user = User::where('id','=',$modelUser->userId)->first();
                            if($user){
                                $user->update([
                                    'resetPasswordToken' => $resetToken,
                                    'createdResetPToken' => date('Y-m-d H:i:s')
                                ]);
                                $response['data']['message'] = 'Request Successfull';
                                $response['data']['result'] = $user->student->getResponseData();
                                $response['data']['resetToken'] = $resetToken;
                                $response['data']['code'] = 200;
                            }
                        }
                    }
                }
                else
                {
                    $response['data']['message'] = 'You have already signup.';
                    //$response['data']['result'] = $user->responder->getResponseData();
                    //$response['data']['resetToken'] = $resetToken;
                    $response['data']['code'] = 401;
                }
            }
        }
        return $response;
    }

    // Update Online Status for User
    public function updateOnlineStatus(Request $request)
    {
        $user = JWTAuth::toUser($request->token);
        // generating default response if something gets wrong.
        $response = [
                'data' => [
                    'code'      => 400,
                    'error'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        // checking if user exists or not
        if(!empty($user) && $user->statusVerified())
        {
            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];
            $rules = [
               'status'   => ['required', 'in:0,1']
            ];
            // valiating that weather status comes from front-end or not And its value should be in 0,1
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $response['status'] = true;
                $response['data']['code'] = 401;
                $isSaved = User::where('id','=',$user->id)
                                ->update(['onlineStatus' => $request->status]);
                // updating status in db and if successfully updated send success response.
                if($isSaved){
                    $response['data']['code'] = 200;
                    $response['data']['message'] = 'Request Successfull';
                }
            }
        }   
        return $response;
    }


    public function splashLogin(Request $request)
    {
        // Login Api for mobile when on splash screen.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified())
        {
            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];

            if($user->isResponder()){
                    
                $response['data']['user'] = base64_encode(json_encode($user->responder->getResponseData()));
                $response['data']['school'] = base64_encode(json_encode($user->responder->schoolProfile->schoolAdminProfile->getResponseData()));
            }
            elseif($user->isStudent()){
                $response['data']['user'] = base64_encode(json_encode($user->student->getResponseData()));
                $response['data']['school'] = base64_encode(json_encode($user->student->schoolProfile->schoolAdminProfile->getResponseData()));
            }
            $response['data']['code'] = 200;
            $response['data']['message'] = 'Request Successfull';
            $response['data']['code'] = 200;
            $response['status']= true;
        }
        return $response;
    }
}
