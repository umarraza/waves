<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiStudent as Student;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiRefferalRequest as RefferalRequest;
use App\Models\Api\ApiAnonimity as Anonimity;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdmin;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;




use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use JWTAuthException;
use JWTAuth;

use Carbon\Carbon;

class RefferController extends Controller
{
	public function RefferNewResponder(Request $request)
	{
		$request['description'] = base64_decode($request->description);
		$request['refferedBy'] = (int)base64_decode($request->refferedBy);
		$request['refferedTo'] = (int)base64_decode($request->refferedTo);
		$request['studentId'] = (int)base64_decode($request->studentId);
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
		     	'refferedBy' 	=> ['required','exists:users,id'],
		     	'refferedTo' 	=> ['required','exists:users,id'],
		     	'studentId'		=> ['required','exists:users,id'],
		     	'description'	=> ['required'],
		  	];
		  	$validator = Validator::make($request->all(), $rules);
		  	if ($validator->fails()) {
		    	$response['data']['message'] = 'Invalid input values.';
		      	$response['data']['errors'] = $validator->messages();
		  	}else
		  	{

		      	$refferedBy= Responder::where('userId','=',$request->refferedBy)->first();

		      	if(!empty($refferedBy) && ($refferedBy->user->isDeleted==0))
		      	{
		      		$schoolTimeZone = $refferedBy->schoolProfile->schoolTimeZone;
		      		
		      		$refferedTo= Responder::where('userId','=',$request->refferedTo)->first();

		      		if(!empty($refferedTo) && ($refferedTo->user->isDeleted==0))
		      		{
		      			if($refferedTo->user->verified)
		      			{
		      				$canReffer = false;
		      				if(($refferedBy->getCategory->levelId==1) && ($refferedTo->getCategory->levelId==2 || $refferedTo->getCategory->levelId==3) )
		      				{
		      					$canReffer = true;
		      				}
		      				elseif(($refferedBy->getCategory->levelId==2) && ($refferedTo->getCategory->levelId==2 || $refferedTo->getCategory->levelId==3) )
		      				{
		      					$canReffer = true;
		      				}
		      				elseif(($refferedBy->getCategory->levelId==3) && ($refferedTo->getCategory->levelId==2 || $refferedTo->getCategory->levelId==3) )
		      				{
		      					$canReffer = true;
		      				}
		      				//return $refferedBy->getCategory->levelId."".$refferedTo->getCategory->levelId; 
		      				if($canReffer==true)
		      				{
			      				$student= Student::where('userId','=',$request->studentId)->first();

					      		if(!empty($student) && ($student->user->isDeleted==0))
					      		{	
					      			$student_responder_old = StudentResponder::
					      								where('studentProfileId','=',$student->id)
					      								->where('responderProfileId','=',$refferedBy->id)
					      								->first();
					      			if(!empty($student_responder_old))
					      			{
					      				$student_responder_new = StudentResponder::
					      								where('studentProfileId','=',$student->id)
					      								->where('responderProfileId','=',$refferedTo->id)
					      								->first();
					      				if(empty($student_responder_new))
					      				{	

						      				$student_responder_new = StudentResponder::create([
					                            'studentProfileId'     	=> $student->id,
					                            'responderProfileId'   	=> $refferedTo->id,
					                            'verified'				=> '0',
					                        ]);
					                        $localCreatedAt=Carbon::now()->format('Y-m-d H:i:s');
					                        $refferalRequest = RefferalRequest::create([
					                            'schoolProfileId'  		=> $refferedBy->schoolProfileId,
					                            'refferedBy'   			=> $refferedBy->id,
					                            'refferedTo'			=> $refferedTo->id,
					                            'studentId'				=> $student->id,
					                            'description'			=> $request->description,
					                            'status'				=> 0,
					                            'localCreatedAt'        => date('Y-m-d H:i:s',strtotime($localCreatedAt.$schoolTimeZone." Hours")),
					                        ]);	

					                        if($student_responder_new && $refferalRequest)
					                        {
                                                if($student_responder_new->responderProfile->schoolProfile->schoolSecondaryAdminProfile!=null)
                                                {
                                                    $secAdmin = $student_responder_new->responderProfile->schoolProfile->schoolSecondaryAdminProfile->user->username;
                                                }
                                                else
                                                {
                                                    $secAdmin = "None";
                                                }			
								      			$response['data']['message'] = 'New Responder Assigned';
							      				$response['data']['code'] = 200;
                                                $response['data']['result']['AdminUserName'] = $student_responder_new->responderProfile->schoolProfile->schoolAdminProfile->user->username;
                                                $response['data']['result']['SecAdminUserName'] = $secAdmin;
                                                $response['data']['result']['message'] = "New Reffer Request from a responder";
							      				$response['status'] = true;
							      				$response['data']['result'] = base64_encode(json_encode($response['data']['result']));
							      			}
							      		}
							      		else
							      		{
							      			$response['data']['message'] = 'The following responder is already assigned to student';
							      			$response['data']['code'] = 400;
							      			$response['status'] = true;	
							      		}
					      			}
					      			else
					      			{
					      				$response['data']['message'] = 'You cant assign new responder to this student';
					      				$response['data']['code'] = 400;
					      				$response['status'] = true;
					      			}
						      	}
				      	      	else
				      	      	{
				      	      		$response['data']['message'] = 'The user is not a student';
				      				$response['data']['code'] = 400;
				      				$response['status'] = true;
				      	      	}
				      	    }
				      	    else
				      	    {
				      	    	$response['data']['message'] = 'You are not allowed to reffer this responder';
			      				$response['data']['code'] = 400;
			      				$response['status'] = true;
				      	    }
			      	    }
			      	    else
			      	    {
      	    	      		$response['data']['message'] = 'The User has not signed up yet!!';
      	    				$response['data']['code'] = 400;
      	    				$response['status'] = true;
			      	    }
			      	}
			      	else
			      	{
			      		$response['data']['message'] = 'The user you are reffering is not responder';
						$response['data']['code'] = 400;
						$response['status'] = true;
			      	}

				}
				else
				{
					$response['data']['message'] = 'The user who is reffering is not a responder';
					$response['data']['code'] = 400;
					$response['status'] = true;
				}
		  	}
		}
		return $response;	
	}


	public function getNewRequests(Request $request)
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

            	$schoolProfile = SchoolProfiles::find($request->schoolProfileId);
            	$schoolTimeZone = $schoolProfile->schoolTimeZone;
            	$requests = RefferalRequest::
            	join('responder_profiles as RefBy', 'RefBy.id','=','requests.refferedBy')
            	->join('responder_profiles as RefTo', 'RefTo.id','=','requests.refferedTo')
            	->join('student_profiles as stu', 'stu.id','=','requests.studentId')
            	->select('requests.id',
            			 'requests.status',
            			 'requests.description',
            			 'RefBy.firstName as RefByFirstName',
            			 'RefBy.lastName as RefByLastName',
            			 'RefBy.responderId as RefByResponderId',
 		       			 'RefTo.firstName as RefToFirstName',
 		       			 'RefTo.lastName as RefToLastName',
 		       			 'RefTo.responderId as RefToResponderId',
 		       			 'stu.firstName as stuFirstName',
 		       			 'stu.lastName as stuLastName',
 		       			 'stu.studentId',
 		       			 'requests.localCreatedAt as createdAt'
 		       			)
				->where('requests.schoolProfileId','=',$request->schoolProfileId)
				->orderBy('requests.id', 'desc')
				//->where('status','=','0')
				->get();

				$arrStudent 	= array();
				$arrResponder 	= array();
				$count = 0;
				foreach($requests as $req)
                {
                    $req['fullNameResponder']= $req->RefByFirstName." ".$req->RefByLastName." (".$req->RefByResponderId.")";
                    $req['fullNameStudent']= $req->stuFirstName." ".$req->stuLastName." (".$req->studentId.")";

                    $arrStudentObj = ['value' => $req['fullNameStudent'],
                						'groupName' => "Students",
                						'orignalValue' => $req['fullNameStudent'].'#Stu'
                					];
                    $arrResponderObj = ['value' => $req['fullNameResponder'],
                						'groupName' => "Responders",
                						'orignalValue' => $req['fullNameResponder'].'#Res'
                					];
                	$req['fullNameResponder']= $req['fullNameResponder']."#Res";
                    $req['fullNameStudent']= $req['fullNameStudent']."#Stu";
                    if($count==0)
                    {
                    	$arrStudent[] = $arrStudentObj;
                    	$arrResponder[] = $arrResponderObj;
                    	$count++;
                    }
                    else
                    {
                    	$stdCount = 0;
                    	$resCount = 0;
                    	for($i=0 ;$i<count($arrStudent);$i++)
		                {
		                	if($arrStudent[$i] == $arrStudentObj)
		                	{
		                		$stdCount++;
		                	}
		                	
		                }
		                for($i=0 ;$i<count($arrResponder);$i++)
		                {
		                	if($arrResponder[$i] == $arrResponderObj)
		                	{
		                		$resCount++;
		                	}
		                }
		                if($stdCount==0)
		                {
		                	$arrStudent[] = $arrStudentObj;
		                }
		                if($resCount==0)
		                {
		                	$arrResponder[] = $arrResponderObj;
		                }	
                    }
                }
                $res = array_merge($arrStudent,$arrResponder);



                if ($requests) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result'] = base64_encode(json_encode($requests));
                    $response['data']['searchList'] = $res;
                    $response['status'] = true;
                }
            }
        }
        return $response;
	}



	public function approveRequests(Request $request)
	{
		$request['requestId'] = (int)base64_decode($request->requestId);
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
               'requestId' =>  ['required','exists:requests,id'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
            	$refferalRequest = RefferalRequest::find($request->requestId);

            	if(!empty($refferalRequest))
            	{
            		if($refferalRequest->status==0)
            		{
            			$student_responder = StudentResponder::
		      							where('studentProfileId','=',$refferalRequest->studentId)
		      							->where('responderProfileId','=',$refferalRequest->refferedTo)
		      							->first();
		      			if(!empty($student_responder))
		      			{
	                        $responder = Responder::find($student_responder->responderProfileId);
	                        $schoolTimeZone = $responder->schoolProfile->schoolTimeZone;

	                        $approvedTime=Carbon::now()->format('Y-m-d H:i:s');;
	            			$refferalRequest->status=1;
	                        $refferalRequest->approvedTime=date('Y-m-d H:i:s',strtotime($approvedTime.$schoolTimeZone." Hours"));
	            			$student_responder->verified=1;

			                if ($refferalRequest->save() && $student_responder->save()) {
			                	
	                            //$message = 'Referring request accepted by '.$user->getName();
			                	$std 	= Student::find($refferalRequest->studentId);
	                            $resBy 	= Responder::find($refferalRequest->refferedBy);
	                            $resTo 	= Responder::find($refferalRequest->refferedTo);

	                            // Send to Student
	                            $message = 'You are being reffered by '.$resBy->user->getName().' to '.$resTo->user->getName();
	                            //$std->user->sendPushNotification($message);
	                            $student_responder->studentProfile->user->sendPushNotification($message,"reffer");
	                            $messageStu =  $message;
	                            //Send to Responder
	                            $message = 'A new Student '.$std->user->getName().' has been reffered to you by '.$resBy->user->getName();
	                            //$resTo->user->sendPushNotification($message);
	                            $student_responder->responderProfile->user->sendPushNotification($message,"reffer");

	                            $messageRes =  $message;
			                    
			                    $response['data']['message'] = 'Request Successfull';
			                    $response['data']['code'] = 200;
			                    $response['data']['result']['studentMessage'] = $messageStu;
			                    $response['data']['result']['responderMessage'] = $messageRes;
			                    $response['data']['result']['responderUsername'] = $resTo->user->username;
			                    $response['data']['result']['studentUsername'] = $std->user->username;
			                    $response['status'] = true;
			                    $response['data']['result'] = base64_encode(json_encode($response['data']['result']));
			                }
			            }
			            else
			            {
			            	$response['data']['message'] = 'Request Declined.';
		                    $response['data']['code'] = 400;
		                    $response['status'] = true;
			            }
		            }
		            else
		            {
		            	$response['data']['message'] = 'Request Already Accepted';
	                    $response['data']['code'] = 400;
	                    $response['status'] = true;
		            }
	            }
	            else
	            {
	            	$response['data']['message'] = 'Request Does not exist';
                    $response['data']['code'] = 400;
                    $response['status'] = true;	
	            }
            }
        }
        return $response;
	}

	public function delRequests(Request $request)
	{
		$request['requestId'] = (int)base64_decode($request->requestId);
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
               'requestId' =>  ['required','exists:requests,id'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
            	$refferalRequest = RefferalRequest::find($request->requestId);

            	if(!empty($refferalRequest))
            	{
            		$student_responder = StudentResponder::
		      							where('studentProfileId','=',$refferalRequest->studentId)
		      							->where('responderProfileId','=',$refferalRequest->refferedTo)
		      							->first();
                    $refferalRequest->status = 2;


	                if ($refferalRequest->save() && $student_responder->delete()) {
	                    $response['data']['message'] = 'Request Successfull';
	                    $response['data']['code'] = 200;
	                    $response['status'] = true;
	                }
		            
	            }
	            else
	            {
	            	$response['data']['message'] = 'Request Does not exist';
                    $response['data']['code'] = 400;
                    $response['status'] = true;	
	            }
            }
        }
        return $response;
	}

	public function analyticsOld(Request $request)
    {
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
                $school = SchoolProfiles::find($request->schoolProfileId);
                $schoolTimeZone = $school->schoolTimeZone;

                $date=array();

                $date[0] = Carbon::now()->startOfWeek()->format('Y-m-d');

                for($i=1; $i<=6 ; $i++ )
                {
                    $date[$i]=Carbon::parse($date[0])->addDay($i)->format('Y-m-d');
                }

                for($i=0; $i<=6 ; $i++ )
                {
                    $date[$i]= date('Y-m-d',strtotime($date[$i].$schoolTimeZone." Hours"));
                }

                $refferal=array();
                $refferal[0] = RefferalRequest::where('schoolProfileId','=',$request->schoolProfileId)
                                        ->whereDate('approvedTime', '=', $date[0])
                                        ->where('status', '=', 1)
                                        ->count();

                for($i=1;$i<=6;$i++)
                {
                    $refferal[$i] = RefferalRequest::where('schoolProfileId','=',$request->schoolProfileId)
                                        ->whereDate('approvedTime', '=', $date[$i])
                                        ->where('status', '=', 1)
                                        ->count();
                }                  
                
                if($refferal) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result'] = $refferal;
                }  
            }
        }
        return $response;
    }


    public function adminSideBarRequestsCount(Request $request)
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
                $requests = RefferalRequest::where('schoolProfileId','=',$request->schoolProfileId)
                                            ->where('status','=','0')
                                            ->count();

                $reports = Anonimity::where('schoolProfileId','=',$request->schoolProfileId)
                                        ->where('status','=','0')
                                        ->count();

                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['data']['result']['refferalCount'] = $requests;
                $response['data']['result']['reportedCount'] = $reports;
                $response['status'] = true;
                
            }
        }
        return $response;
    }

    public function analytics(Request $request)
    {
    	$request['schoolProfileId'] = (int)base64_decode($request->schoolProfileId);
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
               'filter' => ['required'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {

                $school = SchoolProfiles::find($request->schoolProfileId);
                $schoolTimeZone = $school->schoolTimeZone;

                $filterType = $request->filter;

                $count = 0;
                $filterText = "Today";
                if($filterType=="Weekly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $SD=Carbon::parse($dateLocalS)->startOfWeek()->format('Y-m-d');
                    $ED=Carbon::parse($SD)->addDay(6)->format('Y-m-d');

                    $count = RefferalRequest::where('schoolProfileId','=',$school->id)
                                        ->whereDate('approvedTime','>=',$SD)
                                        ->whereDate('approvedTime','<=',$ED)
                                        ->where('status', '=', 1)
                                        ->count();
                    $filterText = "This Week";
                }
                elseif($filterType == "Yearly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $SD=Carbon::parse($dateLocalS)->startOfYear()->format('Y-m-d');
                    $ED=Carbon::parse($SD)->addDay(364)->format('Y-m-d');

                    $count = RefferalRequest::where('schoolProfileId','=',$school->id)
                                        ->whereDate('approvedTime','>=',$SD)
                                        ->whereDate('approvedTime','<=',$ED)
                                        ->where('status', '=', 1)
                                        ->count();
                    $filterText = "This Year";

                }
                elseif($filterType == "Monthly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $SD=Carbon::parse($dateLocalS)->startOfMonth()->format('Y-m-d');
                    $numberOfDays = Carbon::parse($SD)->daysInMonth;
                    $ED=Carbon::parse($SD)->addDay($numberOfDays-1)->format('Y-m-d');

                    $count = RefferalRequest::where('schoolProfileId','=',$school->id)
                                        ->whereDate('approvedTime','>=',$SD)
                                        ->whereDate('approvedTime','<=',$ED)
                                        ->where('status', '=', 1)
                                        ->count();
                    $filterText = "This Month";

                }
                elseif($filterType == "Today")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));
                    $SD=Carbon::parse($dateLocalS)->format('Y-m-d');

                    $count = RefferalRequest::where('schoolProfileId','=',$school->id)
                                        ->whereDate('approvedTime','=',$SD)
                                        ->where('status', '=', 1)
                                        ->count();
                    $filterText = "Today";
                }
                
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result']['count'] = $count;
                    $response['data']['result']['filter'] = $filterText;
                }  
            }
        }
        return $response;
    }    
}
