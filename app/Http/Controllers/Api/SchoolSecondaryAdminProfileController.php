<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuthException;
use JWTAuth;
use App\Http\Requests;
use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdminProfiles;
use App\Models\Api\ApiSchoolSecondaryAdminProfile as SchoolSecondaryAdminProfile;
use App\Models\Api\ApiStudent as Student;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiLocalResource as LocalResource;
use App\Models\Api\ApiCrisisResource as CrisisResource;
use App\Models\Api\ApiResponderCategory as ResponderCategory;

class SchoolSecondaryAdminProfileController extends Controller
{
    public function createSecondaryAdmin(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        //return $request;
    	$user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->isAdmin() && $user->statusVerified())
        {
            $rules = [
                'username'   		=> ['required', 'email', 'max:191', function ($attribute, $value, $fail) {
                                                            $user = User::where('username','=',$value)
                                                            ->where('isDeleted','=',0)
                                                            ->count();
                                                            if ($user>0) {
                                                                $fail($attribute.' already exist.');
                                                        }
                                                    }],
                'schoolProfileId' 	=> ['required'],
                'firstName'  		=> ['required','max:200'],
                'lastName'   		=> ['required','max:200'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {

                $rolesId = Roles::findByAttr('label',User::USER_SECONDARY_ADMIN)->id;
                $password = bin2hex(openssl_random_pseudo_bytes(3));//substr(uniqid('', true), -5);
                $newUser = User::create([
                    'username'  => $request->get('username'),
                    'password'  => bcrypt($password),
                    'roleId'    => $rolesId,
                    'verified'  => User::STATUS_INACTIVE
                ]);
                if($newUser)
                {
                    $modelSchoolSecondaryAdminProfile = SchoolSecondaryAdminProfile::create([
                        'userId'            => $newUser->id,
                        'schoolProfileId'   => $request->schoolProfileId,
                        'firstName'         => $request->get('firstName'),
                        'lastName'          => $request->get('lastName')
                    ]);
                    if($modelSchoolSecondaryAdminProfile && $modelSchoolSecondaryAdminProfile->sendEmail($password))
                    {
                        $response['data']['message'] = 'Request Successfull!!';
                        $response['data']['code'] = 200;
                        $response['status'] = true;
                        //$response['data']['result'] = [ $modelSchoolSecondaryAdminProfile->getResponseData()  ];
                	}
                }
            }
        }
        return $response;
    }


    public function getSecondaryAdmin(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified())
        {
            $rules = [
                'schoolProfileId' => ['required','exists:school_profiles,id'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $schoolSecondaryAdminProfile = SchoolSecondaryAdminProfile::where('schoolProfileId','=', $request->schoolProfileId)
                                                                            ->first();
                $schoolAdminProfile = SchoolAdminProfiles::where('schoolProfileId','=', $request->schoolProfileId)
                                                                            ->first();
                if($schoolSecondaryAdminProfile)
                {
                    $response['data']['message'] = 'Request Successfull!!';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result']['primaryAdmin'] = [ $schoolAdminProfile->getResponseData()];
                    $response['data']['result']['secondaryAdmin'] = [ $schoolSecondaryAdminProfile->getResponseData()];

                    $response['data']['result'] = base64_encode(json_encode($response['data']['result']));
                    
                }
                else
                {
                    $response['data']['message'] = 'Request Successfull!!';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result']['primaryAdmin'] = [ $schoolAdminProfile->getResponseData()];
                    $response['data']['result']['secondaryAdmin'] = [];
                    $response['data']['result'] = base64_encode(json_encode($response['data']['result']));
                }
            }
        }
        return $response;
    }


    public function editSecondaryAdmin(Request $request)
    {
        $request['userId'] = (int)base64_decode($request->userId);
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified())
        {
            $rules = [
                'userId'    => ['required','exists:users,id'],
                'username'  => ['required', 'email'],
                'firstName' => ['required'],
                'lastName' => ['required'],
                'emailStatus' => ['required'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                if($request->emailStatus==1)
                {
                    if($user->isAdmin())
                    {
                        $schoolSecondaryAdminProfile = SchoolSecondaryAdminProfile::where('userId','=', $request->userId)->first();
                        $checkAvailablitiy = User::where('username','=',$request->username)->where('isDeleted','=',0)->first();

                        if(empty($checkAvailablitiy))
                        {
                            $password = bin2hex(openssl_random_pseudo_bytes(3));
                            $userModel = User::find($request->userId);
                            $userModel->username =  $request->username;
                            $userModel->password =  bcrypt($password);
                            $userModel->verified =  0;
                            //$modelSchoolSecondaryAdminProfile =  SchoolAdminProfiles::where('userId','=',$userModel->id)->first();
                            if($userModel->save() && $userModel->schoolSecondaryAdminProfile->sendEmail($password))
                            {
                                $response['data']['message'] = 'Secondary Admin Successfully Updated!!';
                                $response['data']['code'] = 200;
                                $response['status'] = true;
                                //$response['data']['result'] = [ $schoolSecondaryAdminProfile->getResponseData()];
                            } 
                        }
                        else
                        {
                            $response['data']['message'] = 'The email already exist!!';
                            $response['data']['code'] = 400;
                            $response['status'] = true;
                            //$response['data']['result'] = [ $schoolSecondaryAdminProfile->getResponseData()];
                        }
                    }      
                }
                else
                {
                    $schoolSecondaryAdminProfile = SchoolSecondaryAdminProfile::where('userId','=', $request->userId)->first();
                    $schoolSecondaryAdminProfile->firstName = $request->firstName;
                    $schoolSecondaryAdminProfile->lastName = $request->lastName;

                    if($schoolSecondaryAdminProfile->save())
                    {
                        $response['data']['message'] = 'Secondary Admin Successfully Updated!!';
                        $response['data']['code'] = 200;
                        $response['status'] = true;
                        //$response['data']['result'] = [ $schoolSecondaryAdminProfile->getResponseData()];
                    }
                }                
            }
        }
        else
        {
            $response['data']['message'] = 'You are not allowed to change your own email!!!';
            $response['data']['code'] = 400;
            $response['status'] = true; 
        }
        return $response;
    }



    public function deleteSecondaryAdmin(Request $request)
    {
        $request['userId'] = (int)base64_decode($request->userId);
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->isAdmin() && $user->statusVerified())
        {
            $rules = [
                'userId' => ['required','exists:users,id'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $userModel = User::find($request->userId);
                $schoolSecondaryAdminModel = SchoolSecondaryAdminProfile::where('userId','=',$request->userId)->first();

                if($userModel->delete() && $schoolSecondaryAdminModel->delete())
                {
                    $response['data']['message'] = 'Request Successfull!!';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }
}
