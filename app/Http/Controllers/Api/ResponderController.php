<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuthException;
use JWTAuth;
use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiStudent as Student;
use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdminProfiles;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiThreads as Threads;
use App\Models\Api\ApiResponderCategory as ResponderCategory;

use App\Models\Api\ApiScheduleSessions2 as ScheduleSessions2;
use App\Models\Api\ApiRefferalRequest as RefferalRequest;

use App\Models\Api\ApiScheduleAlert as ScheduleAlert;

class ResponderController extends Controller
{

    // Get Responder
    public function getResponder(Request $request)
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
            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];
            $rules = [
               'schoolProfileId' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {


                $responder = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                        //->join('responder_categories', 'responder_categories.id', '=', 'responder_profiles.position')
                                        ->select('responder_profiles.id','responder_profiles.userId','users.username','users.avatarFilePath','responder_profiles.firstName','responder_profiles.lastName','responder_profiles.title','responder_profiles.responderId','responder_profiles.authorizationCode','users.verified','responder_profiles.position')
                                        ->where('schoolProfileId','=',$request->schoolProfileId)
                                        ->where('isDeleted','=',0)
                                        ->get();
                foreach($responder as $res)
                {
                    $res['resNameId']= $res->firstName."(".$res->responderId.")";
                    $res['fullName']= $res->firstName." ".$res->lastName;
                    $res['position']= $res->getCategory->positionName;
                    $res['allData'] = $res->firstName.' '.$res->lastName.' ('.$res->responderId.")";
                }
                
                if ($responder) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result'] = base64_encode(json_encode($responder));
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    // Get Responder Position
    public function getResponderPosition(Request $request)
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
               'userId' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {


                $responder = Responder::where('userId','=',$request->userId)
                                        ->first();
                if ($responder) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result'] = $responder->getCategory->positionName;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    // Get Responder Counselor
    public function getResponderCounselor(Request $request)
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
            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];
            $rules = [
               'schoolProfileId' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {

                $responderCategory =  ResponderCategory::where('schoolProfileId','=',$request->schoolProfileId)
                                                       ->where('levelId','=',1)
                                                        ->first();
                $responder = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                        ->select('responder_profiles.id','responder_profiles.userId','users.username','users.avatarFilePath','responder_profiles.firstName','responder_profiles.lastName','responder_profiles.title','responder_profiles.responderId','responder_profiles.authorizationCode','responder_profiles.position')
                                        ->where('schoolProfileId','=',$request->schoolProfileId)
                                        ->where('position','=',$responderCategory->id)
                                        ->where('isDeleted','=',0)
                                        ->get();

                foreach($responder as $res)
                {
                    $res['resNameId']= $res->firstName."(".$res->responderId.")";
                    $res['position']= $responderCategory->positionName;
                }
                
                if ($responder) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result'] = base64_encode(json_encode($responder));
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    // Get Responder for Responder
    public function getResponderForResponder(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
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
               'schoolProfileId' => 'required',
               'userId' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {


                $responder = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                        ->select('responder_profiles.id','responder_profiles.userId','users.username','users.avatarFilePath','users.onlineStatus','responder_profiles.firstName','responder_profiles.lastName','responder_profiles.title','responder_profiles.responderId','responder_profiles.position','responder_profiles.authorizationCode')
                                        ->where('schoolProfileId','=',$request->schoolProfileId)
                                        ->where('responder_profiles.userId','!=',$request->userId)
                                        ->where('isDeleted','=',0)
                                        ->get();
                foreach($responder as $res)
                {
                    $res['resNameId']= $res->firstName."(".$res->responderId.")";
                    $res['fullName']= $res->firstName." ".$res->lastName;
                    $res['position']= $res->getCategory->positionName;

                }
                
                if ($responder) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result'] = base64_encode(json_encode($responder));
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    // Add Responder
    public function addResponder(Request $request)
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
            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];
            $rules = [
                'responderId'   => ['required', function ($attribute, $value, $fail) {
                                                            $responder = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                                            ->where('isDeleted','=',0)
                                                            ->where('responderId','=',$value)
                                                            ->count();
                                                            if ($responder>0) {
                                                                $fail($attribute.' already exist.');
                                                        }
                                                    }],
                'title'         => 'required',
                'firstName'     => 'required',
                'lastName'      => 'required',
                'username'      => ['required', 'email', 'max:191', function ($attribute, $value, $fail) {
                                                            $user = User::where('username','=',$value)
                                                            ->where('isDeleted','=',0)
                                                            ->count();
                                                            if ($user>0) {
                                                                $fail($attribute.' already exist.');
                                                        }
                                                    }],
                'position'      => 'required',
                'schoolProfileId' => 'required',
               
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {

                $rolesId = Roles::findByAttr('label',User::USER_RESPONDER)->id;

                $authorizationCode = rand(100000,999999);
                // First Enter Data in users Table
                $modelUser = User::create([
                    'username'  => $request->get('username'),
                    'password'  => bcrypt($authorizationCode),
                    'roleId'    => $rolesId,
                    'verified'  => User::STATUS_INACTIVE
                ]);
                // Now Enter Data in responder table
                if($modelUser){
                    //$modelSchoolAdminProfile = SchoolAdminProfiles::find($request->schoolAdminProfileId);
                    if(!empty($request->schoolProfileId)){
                        $responder = Responder::create([
                            'title'                 => $request->get('title'),
                            'firstName'             => $request->get('firstName'),
                            'lastName'              => $request->get('lastName'),
                            'responderId'           => $request->get('responderId'),
                            'userId'                => $modelUser->id,
                            'position'              => $request->get('position'),
                            'schoolProfileId'       => $request->schoolProfileId,
                            'authorizationCode'     => $authorizationCode,
                            'isAvailable'           => 0,
                        ]);
                        
                        if ($responder && $responder->sendEmail()) {
                            $response['data']['code'] = 200;
                            //$response['data']['result'] = $responder;
                            $response['status'] = true;
                            $response['data']['message'] = 'Responder added Successfully';
                            //$response['data']['school'] = $responder->schoolAdminProfile->getResponseData();
                            //$response['data']['school'] = $responder->schoolProfile->schoolAdminProfile->getResponseData();
                            //$response['data']['user'] = $modelUser->getArrayResponse();
                        }
                    }
                }
            }
        }
        return $response;
    }


    // Edit Responder
    public function editResponder(Request $request)
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
               'userId'   => ['required','exists:users,id'], 
               'title'   => 'required',
               'firstName'   => 'required',
               'lastName' => 'required',
               'position' => 'required',
               
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                $responderCheck = Responder::where('userId', $request->get('userId'))->first();
                $studentResponder = StudentResponder::where('responderProfileId','=',$responderCheck->id)->first();

                //if($responderCheck->position == "counselor" || $responderCheck->position == "Counselor"
                        //|| $responderCheck->position == "COUNSELOR")
                if($responderCheck->getCategory->levelId == 1 )
                {
                    //return $responderCheck->getCategory->levelId;
                    $responderCategory = ResponderCategory::find($request->position);
                    //if($request->position == "counselor" || $request->position == "Counselor" || $request->position == "COUNSELOR")
                    if($responderCategory->levelId == 1 )
                    {
                          $responder = Responder::where('userId', $request->get('userId'))
                                                ->update([
                                                    'title'        => $request->get('title'),
                                                    'firstName'    => $request->get('firstName'),
                                                    'lastName'     => $request->get('lastName'),
                                                    'position'     => $request->get('position'),
                                                ]);           
                            
                        if ($responder) {
                            $responderUp = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                        //->join('responder_categories', 'responder_categories.id', '=', 'responder_profiles.position')
                                        ->select('responder_profiles.id','responder_profiles.userId','users.username','users.avatarFilePath','responder_profiles.firstName','responder_profiles.lastName','responder_profiles.title','responder_profiles.responderId','responder_profiles.authorizationCode','users.verified','responder_profiles.position')
                                        ->where('userId','=',$request->userId)
                                        ->where('isDeleted','=',0)
                                        ->get();
                            foreach($responderUp as $res)
                            {
                                $res['resNameId']= $res->firstName."(".$res->responderId.")";
                                $res['fullName']= $res->firstName." ".$res->lastName;
                                $res['position']= $res->getCategory->positionName;
                            }
                            $response['data']['message'] = 'Responder updated Successfull';
                            $response['data']['code'] = 200;
                            $response['data']['result'] = base64_encode(json_encode($responderUp));
                            $response['status'] = true;
                        }
                        
                    }
                    else
                    {
                      

                        if(empty($studentResponder))
                        {
                
                            $responder = Responder::where('userId', $request->get('userId'))
                                                            ->update([
                                                                'title'        => $request->get('title'),
                                                                'firstName'    => $request->get('firstName'),
                                                                'lastName'     => $request->get('lastName'),
                                                                'position'     => $request->get('position'),
                                                            ]);           
                            
                            if ($responder) {
                                $responderUp = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                        //->join('responder_categories', 'responder_categories.id', '=', 'responder_profiles.position')
                                        ->select('responder_profiles.id','responder_profiles.userId','users.username','users.avatarFilePath','responder_profiles.firstName','responder_profiles.lastName','responder_profiles.title','responder_profiles.responderId','responder_profiles.authorizationCode','users.verified','responder_profiles.position')
                                        ->where('userId','=',$request->userId)
                                        ->where('isDeleted','=',0)
                                        ->get();
                                    foreach($responderUp as $res)
                                    {
                                        $res['resNameId']= $res->firstName."(".$res->responderId.")";
                                        $res['fullName']= $res->firstName." ".$res->lastName;
                                        $res['position']= $res->getCategory->positionName;
                                    }
                                $response['data']['message'] = 'Responder updated Successfull';
                                $response['data']['code'] = 200;
                                $response['data']['result'] = base64_encode(json_encode($responderUp));
                                $response['status'] = true;
                            }
                        }
                        else
                        {
                            $response['data']['message'] = 'To change the level of this responder, please re-assign their students to another Level 1 responder.';
                            $response['data']['code'] = 500;
                            $response['data']['errors'] = 'To change the level of this responder, please re-assign their students to another Level 1 responder.';
                        }
                    }
                }
                else
                {
                    $responder = Responder::where('userId', $request->get('userId'))
                                            ->update([
                                                'title'        => $request->get('title'),
                                                'firstName'    => $request->get('firstName'),
                                                'lastName'     => $request->get('lastName'),
                                                'position'     => $request->get('position'),
                                            ]);           
                        
                    if ($responder) {
                        $responderUp = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                        //->join('responder_categories', 'responder_categories.id', '=', 'responder_profiles.position')
                                        ->select('responder_profiles.id','responder_profiles.userId','users.username','users.avatarFilePath','responder_profiles.firstName','responder_profiles.lastName','responder_profiles.title','responder_profiles.responderId','responder_profiles.authorizationCode','users.verified','responder_profiles.position')
                                        ->where('userId','=',$request->userId)
                                        ->where('isDeleted','=',0)
                                        ->get();
                        foreach($responderUp as $res)
                        {
                            $res['resNameId']= $res->firstName."(".$res->responderId.")";
                            $res['fullName']= $res->firstName." ".$res->lastName;
                            $res['position']= $res->getCategory->positionName;
                        }
                        $response['data']['message'] = 'Responder updated Successfull';
                        $response['data']['code'] = 200;
                        $response['data']['result'] = base64_encode(json_encode($responderUp));
                        $response['status'] = true;
                    }
                }
            }
        }
        return $response;
    }

    public function deleteResponder(Request $request)
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
        if(!empty($user) && $user->statusVerified())// && $user->isAdmin())
        {
            $rules = [
                'userId'   => ['required','exists:responder_profiles,userId']
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else{
                $modelUser = User::where('id','=',$request->get('userId'))->first();

                if($modelUser->responder->getCategory->levelId==1)
                {
                    $modelSRCnt = StudentResponder::where('responderProfileId','=',$modelUser->responder->id)->count();
                    if($modelSRCnt >= 1 ){ //&& $modelUser->responder->isCounselor()
                        $response['data']['message'] = 'To remove this responder, please re-assign their students to another Level 1 responder';
                        $response['data']['code'] = 500;
                        $response['data']['errors'] = 'To remove this responder, please re-assign their students to another Level 1 responder.';

                        return $response;
                    }
                    //$modelSRCnt = StudentResponder::where('responderProfileId','=',$modelUser->responder->id)->delete();
                    //$modelResponder = Responder::where('id','=',$modelUser->responder->id)->first();
                    // $modelScheduleAlert = ScheduleAlert::where('toUser','=',$request->get('userId'))->delete();
                }

                $modelUser->isDeleted   = 1;
                $modelUser->verified    = 0;
                $modelUser->username    = $modelUser->username."_".$modelUser->id;
                $modelSR = StudentResponder::where('responderProfileId','=',$modelUser->responder->id)->delete();
                $resoponderDataId = $modelUser->responder->id;

                $currentDate = date('Y-m-d H:i:s');
                $isDeletedSession = ScheduleSessions2::where('responderProfileId','=',$resoponderDataId)
                                ->where('endDate','>=',$currentDate)
                                ->update(['isDeleted' => 1 ]);

                $responderUserId = $modelUser->id;
                $isDeletedThread = Threads::where(function($query) use ($responderUserId) {
                                            return $query->where('fromUser','=',$responderUserId)
                                                        ->orWhere('toUser','=',$responderUserId);
                                        })
                                        ->where('isDeleted','=',0)
                                        ->update(['isDeleted' => 1 ]);

                
                $isDeletedRefferral = RefferalRequest::where(function($query) use ($resoponderDataId) {
                                            return $query->where('refferedBy','=',$resoponderDataId)
                                                        ->orWhere('refferedTo','=',$resoponderDataId);
                                        })
                                        ->where('status','=',0)
                                        ->update(['isDeleted' => 1,'status' => 2 ]);

                //if($modelResponder->delete() && $modelUser->delete()){
                if($modelUser->save()){
                    $response['data']['message'] = 'Responder deleted Successfully';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    // Add Responder
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
            'stdAuthCode' => ['required_if:role,"'.User::USER_STUDENT, 'exists:student_profiles,authorizationCode'],
            'username'          => ['required', 'email', 'max:191', 'exists:users,username']
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
                            ->where('users.username','=',$request->get('username'))
                            ->first();
                $modelSchoolAdminProfile = SchoolAdminProfiles::join('school_profiles', 'school_profiles.id', '=', 'school_admin_profiles.schoolProfileId')
                            ->where('school_admin_profiles.id','=',$request->get('schoolId'))->first();
                if(!empty($modelUser) && !empty($modelSchoolAdminProfile) && $modelUser->isResponder()){
                    if($modelUser->authorizationCode == $request->get('resAuthCode') && $modelSchoolAdminProfile->accessCode == $request->get('accessCode')){
                        $resetToken = md5(time() . $modelUser->id . 'waves');

                        $user = User::where('id','=',$modelUser->userId)->first();
                        if($user){
                            $user->update([
                                'resetPasswordToken' => $resetToken
                            ]);
                            $response['data']['message'] = 'Request Successfull';
                            $response['data']['result'] = $user->getArrayResponse();
                            $response['data']['resetToken'] = $resetToken;
                            $response['data']['code'] = 200;
                        }
                    }
                }
            }
            elseif($request->get('role') == User::USER_STUDENT){
                $modelUser = User::join('student_profiles', 'student_profiles.userId', '=', 'users.id')
                            ->where('users.username','=',$request->get('username'))
                            ->first();
                $modelSchoolAdminProfile = SchoolAdminProfiles::join('school_profiles', 'school_profiles.id', '=', 'school_admin_profiles.schoolProfileId')
                            ->where('school_admin_profiles.id','=',$request->get('schoolId'))->first();
                if(!empty($modelUser) && !empty($modelSchoolAdminProfile) && $modelUser->isStudent()){
                    if($modelUser->authorizationCode == $request->get('stdAuthCode') && $modelSchoolAdminProfile->accessCode == $request->get('accessCode')){
                        $resetToken = md5(time() . $modelUser->id . 'waves');

                        $user = User::where('id','=',$modelUser->userId)->first();
                        if($user){
                            $user->update([
                                'resetPasswordToken' => $resetToken
                            ]);
                            $response['data']['message'] = 'Request Successfull';
                            $response['data']['result'] = $user->getArrayResponse();
                            $response['data']['resetToken'] = $resetToken;
                            $response['data']['code'] = 200;
                        }
                    }
                }
            }
        }
        return $response;
    }

    public function ResponderAssignToStudent(Request $request)
    {
        $request['userId'] = (int)base64_decode($request->userId);
        $user = JWTAuth::toUser($request->token);
        $response = [
              'data' => [
                  'code'      => 400,
                  'error'     => '',
                  'message'   => 'Invalid Token! User Not Found.',
              ],
              'status' => false
          ];
        if(!empty($user) && $user->isStudent() && $user->statusVerified())
        {

            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
                'status' => false
            ];
            $rules = [
                'userId' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $student = Student::where('userId','=',$request->userId)->first();

                if(!empty($student))
                {
                    $studentResponder = Responder::join('users', 'users.id', '=',
                                                        'responder_profiles.userId')
                                ->join('responder_students', 'responder_students.responderProfileId', '=', 'responder_profiles.id')
                                ->select('responder_profiles.*', 'users.username','users.avatarFilePath','users.onlineStatus')
                                ->where('studentProfileId','=',$student->id)
                                ->where('responder_students.verified','=',1)
                                ->where('isDeleted','=',0)
                                ->get();

                    $arrA=array();
                    $count=0;
                    foreach($studentResponder as $res)
                    {
                        $res['fullName']= $res->firstName." ".$res->lastName;
                        $res['position']= $res->getCategory->positionName;
                        $arrA[$count] = $res->id;
                        $count++;
                        $fromUser = $user->id;
                        $toUser = $res->userId;
                        $isExist = Threads::where(function($query) use ($fromUser,$toUser) {
                                        return $query->where('fromUser','=',$fromUser)
                                            ->where('toUser','=',$toUser)
                                            ->where('type','=',0);
                                    })
                                 ->orWhere(function($query) use ($fromUser,$toUser) {
                                        return $query->where('fromUser','=',$toUser)
                                            ->where('toUser','=',$fromUser)
                                            ->where('type','=',0);
                                    })
                                 ->first();

                        $res['chatFlag']= false;
                        $res['threadId']= '';
                        if(!empty($isExist)){
                            $res['chatFlag']= true;
                            $res['threadId']= $isExist->id;
                        }

                        $res['status']= true;                        
                    }
                    
                    $otherResponders =  Responder::join('users', 'users.id', '=', 
                                                        'responder_profiles.userId')
                                ->select('responder_profiles.*', 'users.username','users.avatarFilePath')
                                ->where('schoolProfileId','=',$student->schoolProfile->id)
                                ->where('users.verified','=',1)
                                ->whereNotIn('responder_profiles.id', $arrA)
                                ->where('isDeleted','=',0)
                                ->get();

                    foreach($otherResponders as $res)
                    {
                        $res['fullName']= $res->firstName." ".$res->lastName;
                        $res['position']= $res->getCategory->positionName;
                        $res['chatFlag']= false;
                        $res['status']= false;
                        $res['threadId']= '';
                    }


                    $result = $studentResponder->merge($otherResponders);                
                    
                    if($result) 
                    {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        $response['data']['result'] = base64_encode(json_encode($result));
                        $response['status'] = true;
                    }
                }
                else
                {
                    $response['data']['message'] = 'Student Does not exist';
                    $response['data']['code'] = 400;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }


    public function ResponderNotAssignToStudent(Request $request)
    {
        $request['userId'] = (int)base64_decode($request->userId);
        $request['attachedResponderId'] = (int)base64_decode($request->attachedResponderId);
        $user = JWTAuth::toUser($request->token);
        $response = [
              'data' => [
                  'code'      => 400,
                  'error'     => '',
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
            $rules = [
                'userId' => 'required',
                'attachedResponderId' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $student = Student::where('userId','=',$request->userId)->first();
                //$student = Student::where('userId','=',237)->first();

                if(!empty($student))
                {
                    // $studentResponder = Responder::join('users', 'users.id', '=',
                    //                                     'responder_profiles.userId')
                    //             ->join('responder_students', 'responder_students.responderProfileId', '=', 'responder_profiles.id')
                    //             ->select('responder_profiles.*', 'users.username','users.avatarFilePath','users.onlineStatus')
                    //             ->where('studentProfileId','=',$student->id)
                    //             ->where('responder_students.verified','=',1)
                    //             ->get();

                    $studentResponder = Responder::join('users', 'users.id', '=',
                                                        'responder_profiles.userId')
                                ->join('responder_students', 'responder_students.responderProfileId', '=', 'responder_profiles.id')
                                ->select('responder_profiles.*', 'users.username','users.avatarFilePath','users.onlineStatus')
                                ->where('studentProfileId','=',$student->id)
                                ->where('responder_students.verified','=',1)
                                ->where('isDeleted','=',0)
                                ->get();

                    $arrA=array();
                    $count=0;
                    $responderList=[];

                    foreach($studentResponder as $res)
                    {
                        $res['resNameId']= $res->firstName."(".$res->responderId.")";
                        $arrA[$count] = $res->id;
                        $count++;
                        if($res->id == $request->attachedResponderId)
                        {
                            $res['status']= true;
                            $responderList[] = $res;
                        }                     
                    }

                   
                    
                    $otherResponders =  Responder::join('users', 'users.id', '=', 
                                                        'responder_profiles.userId')
                                ->select('responder_profiles.*', 'users.username','users.avatarFilePath')
                                ->where('schoolProfileId','=',$student->schoolProfile->id)
                                ->where('users.verified','=',1)
                                ->whereNotIn('responder_profiles.id', $arrA)
                                ->where('isDeleted','=',0)
                                ->get();



                    $otherRespondersResult = [];
                    foreach($otherResponders as $res)
                    {
                        $res['resNameId']= $res->firstName."(".$res->responderId.")";
                        $res['status']= false;

                        if($res->getCategory->levelId==1)
                        {
                            $responderList[]=$res;
                        }
                    }

                    //$attachedResponder->merge($otherRespondersResult);              
                    
                    //if($result) 
                    {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        $response['data']['result'] = base64_encode(json_encode($responderList));
                        $response['status'] = true;
                    }
                }
                else
                {
                    $response['data']['message'] = 'Student Does not exist';
                    $response['data']['code'] = 400;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }


    public function ResponderNotAssignToStudentRef(Request $request)
    {
        $request['userId'] = (int)base64_decode($request->userId);
        $user = JWTAuth::toUser($request->token);
        $response = [
              'data' => [
                  'code'      => 400,
                  'error'     => '',
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
            $rules = [
                'userId' => 'required',
                //'attachedResponderId' => 'required',
                'currentResponderLevel' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $student = Student::where('userId','=',$request->userId)->first();
                //$student = Student::where('userId','=',237)->first();

                if(!empty($student))
                {
                    // $studentResponder = Responder::join('users', 'users.id', '=',
                    //                                     'responder_profiles.userId')
                    //             ->join('responder_students', 'responder_students.responderProfileId', '=', 'responder_profiles.id')
                    //             ->select('responder_profiles.*', 'users.username','users.avatarFilePath','users.onlineStatus')
                    //             ->where('studentProfileId','=',$student->id)
                    //             ->where('responder_students.verified','=',1)
                    //             ->get();

                    $studentResponder = Responder::join('users', 'users.id', '=',
                                                        'responder_profiles.userId')
                                ->join('responder_students', 'responder_students.responderProfileId', '=', 'responder_profiles.id')
                                ->select('responder_profiles.*', 'users.username','users.avatarFilePath','users.onlineStatus')
                                ->where('studentProfileId','=',$student->id)
                                ->where('isDeleted','=',0)
                                //->where('responder_students.verified','=',1)
                                ->get();

                    $arrA=array();
                    $count=0;
                    $responderList=[];

                    foreach($studentResponder as $res)
                    {
                        $res['resNameId']= $res->firstName." ".$res->lastName." (".$res->responderId.")";
                        $arrA[$count] = $res->id;
                        $count++;
                        // if($res->id == $request->attachedResponderId)
                        // {
                        //     $res['status']= true;
                        //     $responderList[] = $res;
                        // }                     
                    }

                   
                    
                    $otherResponders =  Responder::join('users', 'users.id', '=', 
                                                        'responder_profiles.userId')
                                ->select('responder_profiles.*', 'users.username','users.avatarFilePath')
                                ->where('schoolProfileId','=',$student->schoolProfile->id)
                                ->where('users.verified','=',1)
                                ->whereNotIn('responder_profiles.id', $arrA)
                                ->where('isDeleted','=',0)
                                ->get();



                    $otherRespondersResult = [];
                    foreach($otherResponders as $res)
                    {
                        $res['resNameId']= $res->firstName." ".$res->lastName." (".$res->responderId.")";
                        $res['status']= false;

                        if($request->currentResponderLevel==1)
                        {
                            if($res->getCategory->levelId==2 || $res->getCategory->levelId==3)
                            {
                                $responderList[]=$res;
                            }   
                        }
                        elseif($request->currentResponderLevel==2)
                        {
                            if($res->getCategory->levelId==2 || $res->getCategory->levelId==3)
                            {
                                $responderList[]=$res;
                            }
                        }
                        elseif($request->currentResponderLevel==3)
                        {
                            if($res->getCategory->levelId==2 || $res->getCategory->levelId==3)
                            {
                                $responderList[]=$res;
                            }
                        }
                        
                    }

                    $emptyStatus=false;

                    if(count($responderList)==0)
                    {
                        $emptyStatus=true;  
                    }

                    //$attachedResponder->merge($otherRespondersResult);              
                    
                    //if($result) 
                    {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        $response['data']['result'] = base64_encode(json_encode($responderList));
                        $response['data']['emptyStatus'] = $emptyStatus;
                        $response['status'] = true;
                    }
                }
                else
                {
                    $response['data']['message'] = 'Student Does not exist';
                    $response['data']['code'] = 400;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }


    public function getResponderCategories(Request $request)
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
        $rules = [
                'schoolProfileId' => 'required',
            ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else
        {
            if(!empty($user) && $user->statusVerified() )//&& $user->isAdmin())
            {
                $catregory_level1 = ResponderCategory::
                                                where('schoolProfileId','=',$request->schoolProfileId)->
                                                where('levelId','=',1)
                                                ->get();
                $catregory_level2 = ResponderCategory::
                                                where('schoolProfileId','=',$request->schoolProfileId)->
                                                where('levelId','=',2)
                                                ->get();
                $catregory_level3 = ResponderCategory::
                                                where('schoolProfileId','=',$request->schoolProfileId)->
                                                where('levelId','=',3)
                                                ->get();
                $catregory_all = ResponderCategory::where('schoolProfileId','=',$request->schoolProfileId)->orderBy('levelId','asc')->get();    
                if ($catregory_level1 && $catregory_level2 && $catregory_level3 && $catregory_all) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result'] =[

                                                    "categoryLevel1" => $catregory_level1,
                                                    "categoryLevel2" => $catregory_level2,
                                                    "categoryLevel3" => $catregory_level3,
                                                    "categoryAll"    => $catregory_all,
                                                ];
                    $response['status'] = true;
                }
                
            }
        }
        return $response;
    }
    //Route::post('add-responder-category', 'Api\ResponderController@addResponderCategory');  // To add category
    public function addResponderCategory(Request $request)
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
        $rules = [
                'schoolProfileId' => ['required','exists:school_profiles,id'],
                'levelId' => 'required',
                'positionName' => 'required'
            ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else
        {
            if(!empty($user) && $user->statusVerified())//&& $user->isAdmin())
            {
                $newCategory = ResponderCategory::create([
                            'schoolProfileId'       => $request->get('schoolProfileId'),
                            'levelId'               => $request->get('levelId'),
                            'positionName'          => $request->get('positionName'),
                        ]);
                if($newCategory)
                {
                    $catregory_level1 = ResponderCategory::
                                                    where('schoolProfileId','=',$request->schoolProfileId)->
                                                    where('levelId','=',1)
                                                    ->get();
                    $catregory_level2 = ResponderCategory::
                                                    where('schoolProfileId','=',$request->schoolProfileId)->
                                                    where('levelId','=',2)
                                                    ->get();
                    $catregory_level3 = ResponderCategory::
                                                    where('schoolProfileId','=',$request->schoolProfileId)->
                                                    where('levelId','=',3)
                                                    ->get();    
                    if ($catregory_level1 && $catregory_level2 && $catregory_level3) {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        $response['data']['result'] =[

                                                        "categoryLevel1" => $catregory_level1,
                                                        "categoryLevel2" => $catregory_level2,
                                                        "categoryLevel3" => $catregory_level3,
                                                    ];
                        $response['status'] = true;
                    }
                }
                
            }
        }
        return $response;
    }


    public function editResponderCategories(Request $request)
    {
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        $rules = [
                'id' => 'required',
                'positionName' => 'required',
            ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else
        {
            if(!empty($user) && $user->statusVerified())//&& $user->isAdmin())
            {
                $responder_categories= ResponderCategory::find($request->id);  
                $responder_categories->positionName =   $request->positionName;
                if ($responder_categories->save() ) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    //$response['data']['result'] = $responder_categories;
                    $response['status'] = true;
                }
                
            }
        }
        return $response;
    }

    public function deleteResponderCategories(Request $request)
    {
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        $rules = [
                'id' => 'required',
            ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else
        {
            if(!empty($user) && $user->statusVerified())//&& $user->isAdmin())
            {
                $responder_categories= ResponderCategory::find($request->id);
                //return $responder_categories;
                $respondersCount  = Responder::where('position',$responder_categories->id)->count();

                if($respondersCount==0)
                {  
                    if ($responder_categories->delete()) {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        //$response['data']['result'] = $responder_categories;
                        $response['status'] = true;
                    }
                    else
                    {
                        $response['data']['message'] = 'Something went wrong.';
                        $response['data']['code'] = 400;
                        //$response['data']['result'] = $responder_categories;
                        $response['status'] = false;
                    }
                }
                else
                {
                    $response['data']['message'] = 'To remove this level, please re-assign their responders to another Level.';
                    $response['data']['code'] = 400;
                    //$response['data']['result'] = $responder_categories;
                    $response['status'] = false;
                }
                
            }
        }
        return $response;
    }

    // Re Send Email
    public function reSendEmailResponder(Request $request)
    {

        $request['userId'] = (int)base64_decode($request->userId);
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
            $rules = [
               'userId' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {


                $responder = Responder::where('userId','=',$request->userId)->first();

                if ($responder->sendEmail()) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    //$response['data']['result'] = $responder;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }


    public function getOnlineStatus(Request $request)
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
        $rules = [
                'userId' => 'required',
            ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else
        {
            if(!empty($user) && $user->statusVerified())//&& $user->isAdmin())
            {
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['data']['result'] = $user->onlineStatus;
                $response['status'] = true;
            }
        }
        return $response; 
    }

}
