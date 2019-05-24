<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/// JWT ///
use JWTAuthException;
use JWTAuth;

/// Models ///
use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdminProfiles;
use App\Models\Api\ApiStudent as Student;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiLocalResource as LocalResource;
use App\Models\Api\ApiCrisisResource as CrisisResource;
use App\Models\Api\ApiResponderCategory as ResponderCategory;

use App\Models\Api\ApiScheduleSessions2 as ScheduleSessions2;
use App\Models\Api\ApiThreads as Threads;
use App\Models\Api\ApiAnonimity as Anonimity;
use App\Models\Api\ApiRefferalRequest as RefferalRequest;
use App\Models\Api\ApiSchoolSecondaryAdminProfile as SchoolSecondaryAdminProfile;

class SchoolController extends Controller
{

    public function list(Request $request)
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
        if(!empty($user) && $user->isSuperAdmin() && $user->statusVerified()){
            $model = SchoolAdminProfiles::get();
            $sData = [];
            foreach ($model as $key => $value) {
                if($value->user->isDeleted==0)
                    $sData[] = $value->getResponseData();
            }
            $response['data']['message'] = 'Request Successfull';
            $response['data']['code'] = 200;
            $response['data']['result'] = base64_encode(json_encode($sData));
            $response['status'] = true;
        }
        return $response;
    }

    /**
    *
    * Get List of schools without token
    *
    **/
    public function listwt(Request $request)
    {
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Something went wrong. Please try again later!',
                ],
                'status' => false
            ];
        $model = SchoolAdminProfiles::get();
        $sData = [];
        foreach ($model as $key => $value) {
            if($value->user->isDeleted==0)
                $sData[] = $value->getResponseData();
        }
        $response['data']['message'] = 'Request Successfull';
        $response['data']['code'] = 200;
        $response['data']['result'] = base64_encode(json_encode($sData));
        $response['status'] = true;
        return $response;
    }

    // Create School.
    public function createSchool(Request $request)
    {
        // Validating User.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        // Checking if user is Super Admin or not.
        if(!empty($user) && $user->isSuperAdmin() && $user->statusVerified()){
            $rules = [
                'username'   => ['required', 'email', 'max:191', function ($attribute, $value, $fail) {
                                                            $user = User::where('username','=',$value)
                                                            ->where('isDeleted','=',0)
                                                            ->count();
                                                            if ($user>0) {
                                                                $fail($attribute.' already exist.');
                                                        }
                                                    }],
                'schoolName' => ['required','max:255', function ($attribute, $value, $fail) {
                                                            $school = SchoolProfiles::where('schoolName','=',$value)->get();
                                                            foreach ($school as $s ) {
                                                                if ($s->schoolAdminProfile->user->isDeleted==0) {
                                                                    $fail($attribute.' already exist.');
                                                            }
                                                            
                                                        }
                                                    }],
                'firstName'  => ['required','max:200'],
                'lastName'   => ['required','max:200']
            ];
            // Rules for Email,School Name, First Name and Last Name.

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else{
                // Getting role for Admin.
                $rolesId = Roles::findByAttr('label',User::USER_ADMIN)->id;
                // Generate random password.
                $password = bin2hex(openssl_random_pseudo_bytes(3));//substr(uniqid('', true), -5);
                $user = User::create([
                    'username'  => $request->get('username'),
                    'password'  => bcrypt($password),
                    'roleId'    => $rolesId,
                    'verified'  => User::STATUS_INACTIVE
                ]);
                // Insert data in user table.
                if($user){
                    // If user table insertion is success.
                    $modelSchoolProfile = new SchoolProfiles();
                    $modelSchoolProfile->schoolName = $request->get('schoolName');
                    $modelSchoolProfile->generateAccessCode();
                    $modelSchoolProfile->save();
                    // Insert data in school table.

                    if($modelSchoolProfile){
                        // If insertion in school table is success.
                        $modelSchoolAdminProfile = SchoolAdminProfiles::create([
                            'userId'            => $user->id,
                            'schoolProfileId'   => $modelSchoolProfile->id,
                            'firstName'         => $request->get('firstName'),
                            'lastName'          => $request->get('lastName')
                        ]);
                        // Insert table in School Admin table.
                        if($modelSchoolAdminProfile){
                            // If School Admin table insertion is success.

                            // Saving Default Data in Responder Category.
                            $modelResponderCategory1 = ResponderCategory::create([
                                'schoolProfileId'     => $modelSchoolProfile->id,
                                'levelId'                   => 1,
                                'positionName'                      => "Counselor"
                            ]);
                            $modelResponderCategory2 = ResponderCategory::create([
                                'schoolProfileId'     => $modelSchoolProfile->id,
                                'levelId'             => 2,
                                'positionName'        => "Psychologist"
                            ]);
                            $modelResponderCategory3 = ResponderCategory::create([
                                'schoolProfileId'     => $modelSchoolProfile->id,
                                'levelId'             => 3,
                                'positionName'        => "Social Worker"
                            ]);

                            // If success Send Success Response with the data needed.
                            if($modelResponderCategory1 && $modelResponderCategory2 && $modelResponderCategory3 && $modelSchoolAdminProfile->sendEmail($password))
                            {
                                $response['data']['message'] = 'School Added Successfully';
                                $response['data']['code'] = 200;
                                $response['status'] = true;
                                $response['data']['result'] = base64_encode(json_encode([
                                    'schoolName'    =>  $modelSchoolAdminProfile->schoolName,
                                    'accessCode'    =>  $modelSchoolProfile->accessCode,
                                    'firstName'     =>  $modelSchoolAdminProfile->firstName,
                                    'lastName'      =>  $modelSchoolAdminProfile->lastName,
                                    'username'      =>  $user->username
                                ]));
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }

    // Edit School
    // public function edit(Request $request)
    // {
    //     $request['schoolId'] = (int)base64_decode($request->schoolId);
    //     // Validating User
    //     $user = JWTAuth::toUser($request->token);
    //     $response = [
    //             'data' => [
    //                 'code'      => 400,
    //                 'errors'     => '',
    //                 'message'   => 'Invalid Token! User Not Found.',
    //             ],
    //             'status' => false
    //         ];
    //     // Checking if user is Super Admin.
    //     if(!empty($user) && $user->isSuperAdmin() && $user->statusVerified()){
    //         $rules = [
    //             'schoolId'   => ['required','exists:school_admin_profiles,id']
    //         ];
    //         // School Id rule.

    //         // Check if schoolName parameter is send and it is not empty.
    //         if($request->get('schoolName') && !empty($request->get('schoolName'))){
    //             $modelSchoolAdminProfile = SchoolAdminProfiles::where(['id' => $request->get('schoolId')])->first();
    //             return $modelSchoolAdminProfile->schoolName;
    //             if($modelSchoolAdminProfile->schoolName != $request->schoolName)
    //                 $rules['schoolName'] = ['required','max:255',"unique:school_profiles,schoolName,".$modelSchoolAdminProfile->schoolProfile->id];
    //         }

    //         // Check if firstName and lastName parameter is send and it is not empty.
    //         if($request->get('firstName') && !empty($request->get('firstName')))
    //             $rules['firstName'] = ['required','max:200'];
    //         if($request->get('lastName') && $request->get('lastName'))
    //             $rules['lastName'] = ['required','max:200'];

    //         $validator = Validator::make($request->all(), $rules);
    //         if ($validator->fails()) {
    //             $response['data']['message'] = 'Invalid input values.';
    //             $response['data']['errors'] = $validator->messages();
    //         }else{
    //             // Finding School data and updating it.
    //             $modelSchoolAdminProfile = SchoolAdminProfiles::where(['id' => $request->get('schoolId')])->first();
    //             $modelSchoolAdminProfile->firstName = (!empty($request->get('firstName'))) ? $request->get('firstName') : $modelSchoolAdminProfile->firstName;
    //             $modelSchoolAdminProfile->lastName = (!empty($request->get('lastName'))) ? $request->get('lastName') : $modelSchoolAdminProfile->lastName;
    //             if($modelSchoolAdminProfile->save()){
    //                 if(!empty($request->get('schoolName'))){
    //                     $modelSchoolProfile = SchoolProfiles::where(['id' => $modelSchoolAdminProfile->schoolProfileId])->first();
    //                     $modelSchoolProfile->schoolName = $request->get('schoolName');
    //                     $modelSchoolProfile->generateAccessCode();
    //                     $modelSchoolProfile->save();
    //                 }
    //                 $response['data']['message'] = 'School updated Successfully';
    //                 $response['data']['code'] = 200;
    //                 $response['status'] = true;
    //                 $response['data']['result'] = base64_encode(json_encode($modelSchoolAdminProfile->getResponseData()));
    //             }
    //         }
    //     }
    //     return $response;
    // }
    public function edit(Request $request)
    {
        $request['schoolId'] = (int)base64_decode($request->schoolId);
        // Validating User
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        // Checking if user is Super Admin.
        if(!empty($user) && $user->isSuperAdmin() && $user->statusVerified())
        {
            $rules = [
                'schoolId'   => ['required','exists:school_profiles,id'],
                'firstName'  => ['required'],
                'lastName'   => ['required'],
                'schoolName' => ['required'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                //$modelSchoolProfile = SchoolProfiles::all();
                $modelSchoolProfile = SchoolProfiles::where(['id' => $request->schoolId])->first();

                if($modelSchoolProfile->schoolName != $request->schoolName)
                {
                    $checkAvailablitiy = SchoolProfiles::where('schoolName',$request->schoolName)->first();
                    if(empty($checkAvailablitiy))
                    {
                        $modelSchoolProfile->schoolName = $request->get('schoolName');
                        $modelSchoolProfile->generateAccessCode();
                        $modelSchoolProfile->save();
                    }
                    else
                    {
                        $modelSchoolAdminProfile = SchoolAdminProfiles::where('schoolProfileId',$checkAvailablitiy->id)->first();

                        //return $modelSchoolAdminProfile->user->isDeleted;
                        if($modelSchoolAdminProfile->user->isDeleted==1)
                        {
                            $modelSchoolProfile->schoolName = $request->get('schoolName');
                            $modelSchoolProfile->generateAccessCode();
                            $modelSchoolProfile->save();
                        }
                        else
                        {
                            $response['data']['message'] = 'School Name Already Exist';
                            $response['data']['code'] = 400;
                            $response['status'] = false;
                            return $response;
                        }
                        
                    }
                }
                $modelSchoolAdminProfile = SchoolAdminProfiles::where('schoolProfileId',$request->get('schoolId'))
                                                                ->update([
                                                                        "firstName" =>$request->firstName,
                                                                        "lastName"  =>$request->lastName,
                                                                    ]);
                if($modelSchoolAdminProfile)
                {
                    $modelSchoolAdminProfile = SchoolAdminProfiles::where('schoolProfileId',$request->get('schoolId'))->first();
                    $response['data']['message'] = 'School updated Successfully';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result'] = base64_encode(json_encode($modelSchoolAdminProfile->getResponseData()));
                }
            }
        }
        return $response;
    }

    public function delete(Request $request)
    {
        $request['schoolId'] = (int)base64_decode($request->schoolId);
        // Validating User.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'    => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        // Checking if user is Super Admin or not.
        if(!empty($user) && $user->isSuperAdmin() && $user->statusVerified()){
            $rules = [
                'schoolId'   => ['required','exists:school_admin_profiles,id']
            ];

            // Validate data.
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else{
                // Find School Admin And other data and then delete them one by one.
                $modelSchoolAdminProfile = SchoolAdminProfiles::where(['id' => $request->get('schoolId')])->first();
                if(!empty($modelSchoolAdminProfile)){
                    $modelStudentProfile = Student::where(['schoolProfileId' => $modelSchoolAdminProfile->schoolProfileId])->get();
                    foreach ($modelStudentProfile as $key => $value) {
                        $userId = $value->userId;
                        $modelRS = StudentResponder::where(['studentProfileId' => $value->id])->delete();
                        // if(!empty($value->delete())){
                        if(!empty($value)){
                            // $modelUser = User::where(['id' => $userId])->delete();
                            // $modelUser = User::where(['id' => $userId])->update([ 'isDeleted' => 1 , 'verified' => 0 , 'username'=>'temp@waves.com' ]);
                            
                            $modelUser = User::find($userId);
                            if($modelUser->isDeleted == 0)
                            {
                                $modelUser->isDeleted   = 1;
                                $modelUser->verified    = 0;
                                $modelUser->username    = $modelUser->username."_".$modelUser->id;
                                $modelUser->save();
                            } 
                        }
                    }

                    $modelResponderProfile = Responder::where(['schoolProfileId' => $modelSchoolAdminProfile->schoolProfileId])->get();
                    foreach ($modelResponderProfile as $key => $value) {
                        $userId = $value->userId;
                        $modelRS = StudentResponder::where(['responderProfileId' => $value->id])->delete();
                        // if(!empty($value->delete())){
                        if(!empty($value)){
                            // $modelUser = User::where(['id' => $userId])->delete();
                            // $modelUser = User::where(['id' => $userId])->update([ 'isDeleted' => 1 , 'verified' => 0 , 'username'=>'temp@waves.com' ]);
                            
                            $modelUser = User::find($userId);
                            if($modelUser->isDeleted == 0)
                            {
                                $modelUser->isDeleted   = 1;
                                $modelUser->verified    = 0;
                                $modelUser->username    = $modelUser->username."_".$modelUser->id;
                                $modelUser->save();
                            }
                        }
                    }

                    //$schoolProfileId = $modelSchoolAdminProfile->schoolProfileId;
                    $userIdAdmin = $modelSchoolAdminProfile->userId;

                    $modelL = LocalResource::where(['schoolProfileId' => $request->get('schoolId')])->delete();
                    $modelC = CrisisResource::where(['schoolProfileId' => $request->get('schoolId')])->delete();

                    //$modelSchoolAdminProfile->delete();

                    //$modelResponderCategory = ResponderCategory::where(['schoolProfileId' => $request->get('schoolId')])->delete();

                    $modelSchoolProfile = SchoolProfiles::where(['id' => $request->get('schoolId')])->first();

                    $modelSchoolSecondaryAdminProfile = SchoolSecondaryAdminProfile::where(['schoolProfileId' => $request->get('schoolId')])->first();
                    if(!empty($modelSchoolSecondaryAdminProfile))
                    {
                        // $modelSchoolSecondaryAdminUserModel = User::where('id','=',$modelSchoolSecondaryAdminProfile->userId)->update([ 'isDeleted' => 1 , 'verified' => 0 , 'username'=>'temp@waves.com']);

                        $modelSchoolSecondaryAdminUserModel = User::find($modelSchoolSecondaryAdminProfile->userId);
                        $modelSchoolSecondaryAdminUserModel->isDeleted   = 1;
                        $modelSchoolSecondaryAdminUserModel->verified    = 0;
                        $modelSchoolSecondaryAdminUserModel->username    = $modelSchoolSecondaryAdminUserModel->username."_".$modelSchoolSecondaryAdminUserModel->id;
                        $modelSchoolSecondaryAdminUserModel->save();
                    }
                    // if(!empty($modelSchoolProfile))
                    //     $modelSchoolProfile->delete();

                    // $modelUser = User::where(['id' => $userIdAdmin])->delete();
                    // $modelUser = User::where(['id' => $userIdAdmin])->update([ 'isDeleted' => 1 , 'verified' => 0 , 'username'=>'temp@waves.com' ]);
                    $modelUser = User::find($userIdAdmin);
                    $modelUser->isDeleted   = 1;
                    $modelUser->verified    = 0;
                    $modelUser->username    = $modelUser->username."_".$modelUser->id;
                    $modelUser->save();

                    $isDeletedSession = ScheduleSessions2::where('schoolProfileId','=',$request->schoolId)->where('isDeleted','=',0)->update(['isDeleted' => 1 ]);
                    
                    $isDeletedThread = Threads::where('schoolProfileId','=',$request->schoolId)
                                                            ->where('isDeleted','=',0)
                                                            ->update(['isDeleted' => 1 ]);
                    
                    $isDeletedRefferral = RefferalRequest::where('schoolProfileId','=',$request->schoolId)->where('isDeleted','=',0)->update(['isDeleted' => 1,'status' => 2 ]);
                    
                    $isDeletedAnonimity = Anonimity::where('schoolProfileId','=',$request->schoolId)
                                                            ->where('isDeleted','=',0)
                                                            ->update(['isDeleted' => 1]);


                    $response['data']['message'] = 'School deleted Successfully';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    // Edit School Name.
    public function editSchoolName(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        // Validating User
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified()){
            $rules = [
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
                'schoolName'    => ['required','unique:school_profiles,schoolName'],
            ];
            // Validating schoolid and schoolname
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                $nameValidator = SchoolProfiles::where('schoolName','=', $request->schoolName)->first();
                // Checking weather the school name is unique or not.
                if(empty($nameValidator))
                {   
                    // If unique then find school.
                    $school_profiles = SchoolProfiles::find($request->schoolProfileId);


                    // update school name.
                    $school_profiles->schoolName = $request->schoolName;
                    
                    // Save data and return response.
                    if($school_profiles->save())
                    {
                        $response['data']['message'] = 'School Name Updated Successfully';
                        $response['data']['code'] = 200;
                        $response['status'] = true;
                        $response['data']['result'] = base64_encode(json_encode([
                            'schoolName'    =>  $school_profiles->schoolName,
                            'accessCode'    =>  $school_profiles->accessCode,
                        ]));
                    }
                }
                else
                {
                    $response['data']['message'] = 'School Name Already Exist';
                    $response['data']['code'] = 400;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }


    // School Admin Name Update.
    public function editSchoolAdminName(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        // Validating user 
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified()){
            $rules = [
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
                'firstName'         => ['required'],
                'lastName'          => ['required'],
            ];
            // Validating rules for schoolId, First and Last Name.
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                // Finding School Admin Data through School Id.
                $schoolAdminProfiles = SchoolAdminProfiles::where('schoolProfileId','=',$request->schoolProfileId)->first();

                // Update the names.
                $schoolAdminProfiles->firstName = $request->firstName;
                $schoolAdminProfiles->lastName  = $request->lastName;
                
                // Saving The updates and sending response
                if($schoolAdminProfiles->save())
                {
                    $response['data']['message'] = 'School Name Updated Successfully';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result'] = [
                        'firstName'     =>  $schoolAdminProfiles->firstName,
                        'lastName'      =>  $schoolAdminProfiles->lastName,
                        'fullName'      =>  $schoolAdminProfiles->fullName(),
                    ];
                }
            }
        }
        return $response;
    }

    // Edit School Admin Email
    public function editSchoolAdminEmail(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        // Validating User.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        // Only Secondary Admin Can update Admin email.
        if(!empty($user) && $user->isSecondaryAdmin() && $user->statusVerified())
        {
            $rules = [
                'userId'            => ['required','exists:users,id'],
                'username'          => ['required','email'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                // Find Admin id.
                $schoolAdminUserId = $user->schoolSecondaryAdminProfile->schoolProfile->schoolAdminProfile->user->id;
                $checkAvailablitiy = User::where('username','=',$request->username)
                                    ->where('isDeleted','=',0)
                                    ->first();
                //  Check if email exists or not.
                if(empty($checkAvailablitiy))
                {
                    // Generating Random Password.
                    $password = bin2hex(openssl_random_pseudo_bytes(3));
                    $userModel = User::find($schoolAdminUserId);
                    $userModel->username =  $request->username;
                    $userModel->password =  bcrypt($password);
                    $userModel->verified =  0;

                    $modelSchoolAdminProfile =  SchoolAdminProfiles::where('userId','=',$userModel->id)->first();
                    // Saving data and sending mail to new email address.
                    if($userModel->save() && $modelSchoolAdminProfile->sendEmail($password))
                    {
                        $response['data']['message'] = 'Primary Admin  Email Successfully Updated!';
                        $response['data']['code'] = 200;
                        $response['status'] = true;
                        $response['data']['result'] = [ $userModel->username];
                    } 
                }
                else
                {
                    $response['data']['message'] = 'The Email Already Exist!';
                    $response['data']['code'] = 400;
                    $response['status'] = true; 
                }
            }
        }
        else
        {
            $response['data']['message'] = 'You are not allowed to change your own email!';
            $response['data']['code'] = 400;
            $response['status'] = true; 
        }
        return $response;
    }

    // School Address Update.
    public function editSchoolAddress(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        // Validating User.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified()){
            $rules = [
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
                'schoolAddress'     => ['required'],
            ];
            // validating data
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                // Finding School and updating address.
                $schoolProfiles = SchoolProfiles::find($request->schoolProfileId);

                $schoolProfiles->schoolAddress = $request->schoolAddress;
                
                if($schoolProfiles->save())
                {
                    $response['data']['message'] = 'School Address Updated Successfully';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result'] = [
                        'schoolAddress' =>  $schoolProfiles->schoolAddress,
                    ];
                }
            }
        }
        return $response;
    }
    // Updating School Time Zone.
    public function editSchoolTimeZone(Request $request)
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
        if(!empty($user) && $user->statusVerified()){
            $rules = [
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
                'schoolTimeZone'     => ['required'],
            ];
            // Validating school id and timezone.
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                $schoolProfiles = SchoolProfiles::find($request->schoolProfileId);

                // exploding time zone according to our need and saving them.
                $timeZone = explode('@', $request->schoolTimeZone);

                $schoolProfiles->schoolTimeZone = $timeZone[0].$timeZone[1];
                $schoolProfiles->schoolTimeSymbol = $timeZone[0];
                $schoolProfiles->schoolTimeValue = $timeZone[1];
                $schoolProfiles->schoolTimeArea = $timeZone[2];
                

                if($schoolProfiles->save())
                {
                    $response['data']['message'] = 'School Timezone Updated Successfully';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result'] = [
                        'schoolTimeZone' =>  $schoolProfiles->schoolTimeArea,
                        'schoolTimeValue' =>  $schoolProfiles->schoolTimeValue,
                        'schoolTimeSymbol' =>  $schoolProfiles->schoolTimeSymbol,
                        'schoolTimeArea' =>  $schoolProfiles->schoolTimeArea,
                    ];
                }
            }
        }
        return $response;
    }

    // School Logo Updation
    public function editSchoolLogo(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        // Validating rules.
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
                'schoolProfileId' => 'required|exists:school_profiles,id',
                'image' => 'required',
            ];
            // validating 
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                // finding school and updating logo.
                $schoolProfile = SchoolProfiles::find($request->schoolProfileId);

                $file_data = $request->input('image');
                 
                @list($type, $file_data) = explode(';', $file_data);
                @list(, $file_data) = explode(',', $file_data); 
                @list(, $type) = explode('/', $type); 
                //str_replace('/', '.', $type);
                $file_name = 'image_'.time().'.'.$type; //generating unique file name;
                //return $type;
                if($file_data!=""){ // storing image in storage/app/public Folder 
                    \Storage::disk('public')->put($file_name,base64_decode($file_data)); 
                    
                    $schoolProfile->schoolLogo= $file_name;

                    if ($schoolProfile->save())
                    {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        $response['data']['result'] = $schoolProfile->schoolLogo;
                        $response['status'] = true;
                    }
                }
                else
                {
                        $response['data']['message'] = 'File Required';
                        $response['data']['code'] = 400;
                        //$response['data']['result'] = $user->avatarFilePath;
                        $response['status'] = true;
                }
            }
        }
        return $response;
    }

    // Total Student Count to show on update network screen.
    public function getTotalStudentsCount(Request $request)
    {
        $request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
        // validating user.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified()){
            $rules = [
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
            ];
            // validating school id.
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                // Count data and send response.
                $studentCount = Student::where('schoolProfileId','=',$request->schoolProfileId)->count();

                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['status'] = true;
                $response['data']['result'] = $studentCount;
            }
        }
        return $response;    
    }

}
