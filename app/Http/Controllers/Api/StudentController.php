<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use JWTAuthException;
use JWTAuth;

use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiStudent as Student;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdminProfiles;
use App\Models\Api\ApiScheduleSessions2 as ScheduleSessions2;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiThreads as Threads;
use App\Models\Api\ApiRefferalRequest as RefferalRequest;

use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class StudentController extends Controller
{


	// Get Students
    public function getStudents(Request $request)
    {
    	$request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
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
		        	'error' => 400,
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

		      	//$schoolAdminProfiles= SchoolAdminProfiles::find($request->schoolAdminProfileId);

		      	if(!empty($request->schoolProfileId))
		      	{
			      	// $student =  DB::table('student_profiles')
						   //          ->join('users', 'users.id', '=', 'student_profiles.userId')
						   //          ->join('responder_students', 'responder_students.studentProfileId', '=', 'student_profiles.id')
						   //          ->join('responder_profiles', 'responder_profiles.id', '=', 'responder_students.responderProfileId')
						   //          ->select('student_profiles.*','student_profiles.firstName as fullName', 'users.username','responder_profiles.responderId','responder_profiles.firstName as ResFirstName','users.verified')
						   //          ->where('student_profiles.schoolProfileId','=',$request->schoolProfileId)
						   //          ->get();
		      		$student =  DB::table('student_profiles')
						            ->join('users', 'users.id', '=', 'student_profiles.userId')
						            ->select('student_profiles.*','users.*','student_profiles.id as studentProfileId')
						            ->where('student_profiles.schoolProfileId','=',$request->schoolProfileId)
						            ->where('isDeleted','=',0)
						            ->get();

		            //Student::where('schoolProfileId','=',$schoolAdminProfiles->schoolProfileId)->get();
					foreach($student as $std)
					{
						$std->fullName= $std->firstName." ".$std->lastName;
						$std->allData = $std->firstName." ".$std->lastName." (".$std->studentId.")";

						$student_responder = StudentResponder::where('studentProfileId','=',$std->studentProfileId)
															->where('verified','=',1)->first();
						//return $student_responder;

						$responder = Responder::find($student_responder->responderProfileId);
						$std->responderAttached= $responder;
					}
			      
				    if ($student) {
				        $response['data']['message'] = 'Request Successfull';
				        $response['data']['code'] = 200;
				        $response['data']['result'] = base64_encode(json_encode($student));
				        $response['status'] = true;
				    }
				}
			}
		}
		return $response;
	}



	// Add Students
    public function createStudents(Request $request)
    {
    	$request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
    	$request['designatedResponder'] = (int)base64_decode($request->designatedResponder);
    	$user = JWTAuth::toUser($request->token);
    	$response = [
    	        'data' => [
    	            'code'      => 400,
    	            'error'     => '',
    	            'message'   => 'Invalid Token! User Not Found.',
    	        ],
    	        'status' => false
    	    ];
    	if(!empty($user) && $user->statusVerified())// && $user->isAdmin())
    	{
	        $response = [
	            'data' => [
	                'code' => 400,
	               	'message' => 'Something went wrong. Please try again later!',
	            ],
	           'status' => false
	        ];
	        $rules = [
	           'username'   => ['required', 'email', 'max:191',function ($attribute, $value, $fail) {
                                                            $user = User::where('username','=',$value)
                                                            ->where('isDeleted','=',0)
                                                            ->count();
                                                            if ($user>0) {
                                                                $fail($attribute.' already exist.');
                                                        }
                                                    }],
	           'designatedResponder'   => 'required',
	           'firstName' => 'required',
	           'lastName' => 'required',
	           'gradeLevel' => 'required',
	           'schoolProfileId' => 'required',
	           'studentId' => ['required', function ($attribute, $value, $fail) {
                                                            $student = Student::join('users', 'users.id', '=', 'student_profiles.userId')
                                                            ->where('isDeleted','=',0)
                                                            ->where('studentId','=',$value)
                                                            ->count();
                                                            if ($student>0) {
                                                                $fail($attribute.' already exist.');
                                                        }
                                                    }],
	        ];

	        $validator = Validator::make($request->all(), $rules);
	        if ($validator->fails()) {
	            $response['data']['message'] = 'Invalid input values.';
	            $response['data']['errors'] = $validator->messages();
	        }else
	        {
	        	// First Check weather the Responder Is counselor or not
	            $responderProfile = Responder::where('userId','=',$request->get('designatedResponder'))->first();

	            

	            //if($responderProfile->getCategory->levelId == "counselor" || $responderProfile->position == "Counselor"
	        			//|| $responderProfile->position == "COUNSELOR")
	            if($responderProfile->getCategory->levelId == 1 )
	            {

	            			           // echo $request->schoolAdminProfileId;
	            //exit();
	            	$rolesId = Roles::findByAttr('label','student')->id;
		            $authorizationCode = rand(100000,999999);
		            // Second Enter Data in users Table
		            $user = User::create([
		                'username'  => $request->get('username'),
		                'password'  => bcrypt($authorizationCode),
		                'roleId'    => $rolesId,
		                'verified'  => User::STATUS_INACTIVE
		            ]);

		            // Third Get schoolAdminProfiles Table schoolProfileId


		            //$schoolAdminProfiles= SchoolAdminProfiles::find($request->schoolAdminProfileId);

		            
		            // Fourth Enter Data in studentProfiles Table
		            $studentProfile = Student::create([
		                'userId'  			=> $user->id,
		                'schoolProfileId'  	=> $request->schoolProfileId,
		                'studentId'    		=> $request->get('studentId'),
		                'firstName'    		=> $request->get('firstName'),
		                'lastName'    		=> $request->get('lastName'),
		                'gradeLevel'    	=> $request->get('gradeLevel'),
		                'authorizationCode' => $authorizationCode
		            ]);

		            // Fifth Enter Data in responder_students Table
		            $studentResponder = StudentResponder::create([
		                'studentProfileId'  	=> $studentProfile->id,
		                'responderProfileId'    => $responderProfile->id,
		            ]);
		            

		            if ($user && $studentProfile && $studentResponder && $studentProfile->sendEmail() ) {
		                $response['data']['message'] = 'Request Successfull';
		                $response['data']['code'] = 200;
		                //$response['data']['result'] = $user->getArrayResponse();
		                $response['status'] = true;
		            }
	        	}
	        	else
	        	{
	        		$response = [
	        		    'data' => [
	        		        'code' => 400,
	        		       	'message' => 'This responder is not a Primary Responder!',
	        		    ],
	        		   'status' => false
	        		];	
	        	}
			}
		}   
        return $response;
    }

    // Edit Responder
    public function editStudents(Request $request)
    {
    	$request['userId'] = (int)base64_decode($request->userId);
    	$request['designatedResponder'] = (int)base64_decode($request->designatedResponder);
    	$request['oldDesignatedRes'] = (int)base64_decode($request->oldDesignatedRes);
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
               'userId'   => ['required','exists:users,id'], 
               'firstName'   => 'required',
               'lastName'   => 'required',
               'gradeLevel' => 'required',
               'designatedResponder' => ['required','exists:users,id'],
               'oldDesignatedRes' => ['required','exists:users,id'],
               
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {

            	$responder = Responder::where('userId', $request->get('designatedResponder'))->first();
            	$responderOld = Responder::where('userId', $request->get('oldDesignatedRes'))->first();
            	// First Check weather the Responder Is counselor or not

            	if(!empty($responder) && !empty($responderOld))
            	{
		            //if($responder->position == "counselor" || $responder->position == "Counselor"
		        			//|| $responder->position == "COUNSELOR")
		            if($responder->getCategory->levelId == 1 )
		            {

	  	                $student = Student::where('userId', $request->get('userId'))
		                                                ->update([
		                                                    'firstName'    => $request->get('firstName'),
		                                                    'lastName'     => $request->get('lastName'),
		                                                    'gradeLevel'     => $request->get('gradeLevel'),
		                                                ]);
		                $studentData = Student::where('userId', $request->get('userId'))->first();

		                $studentResponder = StudentResponder::where('studentProfileId', $studentData->id)->where('responderProfileId', $responderOld->id)
		                                                ->update(['responderProfileId'=> $responder->id]);

		                
		                if ($student && $studentResponder) {

		                	$studentUp =  DB::table('student_profiles')
						            ->join('users', 'users.id', '=', 'student_profiles.userId')
						            ->select('student_profiles.*','users.*','student_profiles.id as studentProfileId')
						            ->where('student_profiles.userId','=',$request->userId)
						            ->where('isDeleted','=',0)
						            ->get();

				            foreach($studentUp as $std)
							{
								$std->fullName= $std->firstName." ".$std->lastName;

								$student_responder = StudentResponder::where('studentProfileId','=',$std->studentProfileId)
																	->where('verified','=',1)
																	->first();
								$responder = Responder::find($student_responder->responderProfileId);
								$std->responderAttached= $responder;
							}
		                    $response['data']['message'] = 'Request Successfull';
		                    $response['data']['code'] = 200;
		                    $response['data']['result'] = base64_encode(json_encode($studentUp));
		                    $response['status'] = true;
		                }
		            }
		            else
		            {
		            	$response['data']['message'] = 'The Responder You Assign is not a Primary Responder';
	                    $response['data']['code'] = 400;
	                    $response['status'] = true;
		            }
		        }
		        else
		        {
		        	$response['data']['message'] = 'Responder Dont Exist';
                    $response['data']['code'] = 400;
                    $response['status'] = true;
		        }
            }
        }
        return $response;
    }

    // Del Local Resource
    public function delStudents(Request $request)
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
      if(!empty($user) && $user->statusVerified())// && $user->isAdmin())
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
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else
        {
        	$student = User::where('id', $request->get('userId'))->first();
        
            $studentData = Student::where('userId', $request->get('userId'))->first();
        	$studentResponder = StudentResponder::where('studentProfileId', $studentData->id)->delete();

            // foreach ($studentResponder as $SR ) {
            // 	$SR->delete();
            // }
            //$studentResponder->delete();
            //$studentData->delete();
            $student->isDeleted = 1;
            $student->verified = 0;
            $deletedMail =  $student->username."_".$request->userId;
            $student->username = $deletedMail;
            $currentDate = date('Y-m-d H:i:s');
            $isDeletedSession = ScheduleSessions2::where('studentProfileId','=',$studentData->id)
                            ->where('endDate','>=',$currentDate)
                            ->update(['isDeleted' => 1 ]);
            $studentUserId = $student->id;
        	$isDeletedThread = Threads::where(function($query) use ($studentUserId) {
                                    	return $query->where('fromUser','=',$studentUserId)
                                           			->orWhere('toUser','=',$studentUserId);
                                    })
            						->where('isDeleted','=',0)
            						->update(['isDeleted' => 1 ]);
           	$isDeletedRefferral = RefferalRequest::where('studentId','=',$studentData->id)
            						->where('status','=',0)
            						->update(['isDeleted' => 1,'status' => 2 ]);

            //$student->delete();
            // if ($studentData && $studentResponder && $student) {
            if ($student->save() && $studentResponder) {
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                //$response['data']['result'] = $localResource;
                $response['status'] = true;
            }
        }
      }
      return $response;
    }



    public function StudentAssignToResponder(Request $request)
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
		if(!empty($user) && $user->isResponder() && $user->statusVerified())
		{

		  	$response = [
		      	'data' => [
		          	'error' => 400,
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

		  		if(!empty($responder))
		  		{
		  			$student =  DB::table('student_profiles')
					            ->join('users', 'users.id', '=', 'student_profiles.userId')
					            ->join('responder_students', 'responder_students.studentProfileId', '=', 'student_profiles.id')
					            // ->join('responder_profiles', 'responder_profiles.id', '=', 'responder_students.responderProfileId')
					            ->select('student_profiles.*','student_profiles.firstName as fullName','users.username','users.avatarFilePath')
					            ->where('responderProfileId','=',$responder->id)
					            ->where('responder_students.verified','=',1)
					            ->where('isDeleted','=',0)
					            ->get();
					foreach($student as $std)
					{
						$std->fullName= $std->firstName." ".$std->lastName;
					}
					if($student) 
					{
			          	$response['data']['message'] = 'Request Successfull';
			          	$response['data']['code'] = 200;
			          	$response['data']['result'] = base64_encode(json_encode($student));
			          	$response['status'] = true;
			    	}
		  		}
		  		else
		  		{
		  			$response['data']['message'] = 'Reponder Does not exist';
		  			$response['data']['code'] = 400;
		  			$response['status'] = true;
		  		}
		  	}
		}
		return $response;
    }

    // Re Send Email Student
    public function reSendEmailStudent(Request $request)
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


                $responder = Student::where('userId','=',$request->userId)->first();

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

    public function analyticsOld(Request $request)
    {
    	$request['schoolProfileId'] = base64_decode($request->schoolProfileId);
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
               'schoolProfileId'    => ['required','exists:school_profiles,id'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $active = User::where('roleId','=','4')
	                            ->where('verified', '=', 1)
	                            ->get();
	            $activeList=[];
	            foreach($active as $a) {
	            	$activeList[]=$a->id;
	            }
                $activeStudents = Student::whereIn('userId',$activeList)
                						->where('schoolProfileId','=',$request->schoolProfileId)
                						->count();


                $inActive = User::where('roleId','=','4')
	                            ->where('verified', '=', 0)
	                            ->get();
	            $inActiveList=[];
	            foreach($inActive as $a) {
	            	$inActiveList[]=$a->id;
	            }
                $inActiveStudents = Student::whereIn('userId',$inActiveList)
                						->where('schoolProfileId','=',$request->schoolProfileId)
                						->count();	
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['status'] = true;
                $response['data']['result'][]= $activeStudents;
                $response['data']['result'][]= $inActiveStudents;
            }
        }
        return $response;
    }
    public function analytics(Request $request)
    {
    	$request['schoolProfileId'] = base64_decode($request->schoolProfileId);
    	$request['filter'] = base64_decode($request->filter);
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
               'schoolProfileId'    => ['required','exists:school_profiles,id'],
               'filter'				=> ['required']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
            	if($request->filter=="Student")
            	{
	                $active = User::where('roleId','=','4')
		                            ->where('verified', '=', 1)
		                            ->get();
		            $activeList=[];
		            foreach($active as $a) {
		            	$activeList[]=$a->id;
		            }
	                $activeStudents = Student::whereIn('userId',$activeList)
	                						->where('schoolProfileId','=',$request->schoolProfileId)
	                						->count();


	                $inActive = User::where('roleId','=','4')
		                            ->where('verified', '=', 0)
		                            ->get();
		            $inActiveList=[];
		            foreach($inActive as $a) {
		            	$inActiveList[]=$a->id;
		            }
	                $inActiveStudents = Student::whereIn('userId',$inActiveList)
	                						->where('schoolProfileId','=',$request->schoolProfileId)
	                						->count();
	            	$response['data']['result']["Active"]	= $activeStudents;
	                $response['data']['result']["Inactive"]	= $inActiveStudents;
	                $response['data']['result']["Filter"]	= "Student";
	            }
	            else
	            {
	            	$active = User::where('roleId','=','3')
		                            ->where('verified', '=', 1)
		                            ->get();
		            $activeList=[];
		            foreach($active as $a) {
		            	$activeList[]=$a->id;
		            }
	                $activeResponders = Responder::whereIn('userId',$activeList)
	                						->where('schoolProfileId','=',$request->schoolProfileId)
	                						->count();


	                $inActive = User::where('roleId','=','3')
		                            ->where('verified', '=', 0)
		                            ->get();
		            $inActiveList=[];
		            foreach($inActive as $a) {
		            	$inActiveList[]=$a->id;
		            }
	                $inActiveResponders = Responder::whereIn('userId',$inActiveList)
	                						->where('schoolProfileId','=',$request->schoolProfileId)
	                						->count();
					$response['data']['result']['Active']	= $activeResponders;
					$response['data']['result']['Inactive']	= $inActiveResponders;
					$response['data']['result']["Filter"]	= "Responder";
	            }	
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['status'] = true;
            }
        }
        return $response;
    }
}
