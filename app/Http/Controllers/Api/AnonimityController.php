<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Api\ApiStudent as Student;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiAnonimity as Anonimity;
use App\Models\Api\ApiThreads as Threads;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use JWTAuthException;
use JWTAuth;

use Carbon\Carbon;

class AnonimityController extends Controller
{
	public function getAnonimityRequest(Request $request)
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
            	'schoolProfileId' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
            	$reports = Anonimity::
            	join('responder_profiles as responder', 'responder.userId','=','anonimity_reports.responderId')
            	->join('student_profiles as student', 'student.userId','=','anonimity_reports.studentId')
            	->join('user_threads as thread', 'thread.id','=','anonimity_reports.threadId')
            	->select('anonimity_reports.id',
            			 'anonimity_reports.status',
            			 'anonimity_reports.message',
            			 'anonimity_reports.comment',
            			 'anonimity_reports.threadId',
            			 'anonimity_reports.localCreatedAt as createdAt',
            			 'responder.firstName as responderFirstName',
            			 'responder.lastName as responderLastName',
 		       			 'student.firstName as studentFirstName',
 		       			 'student.lastName as studentLastName',
 		       			 'student.studentId',
 		       			 'thread.causeData'
 		       			)
				//->where('anonimity_reports.schoolAdminProfileId','=',$user->schoolAdminProfile->id)
                ->where('anonimity_reports.schoolProfileId','=',$request->schoolProfileId)
                ->orderBy('anonimity_reports.id', 'desc')				
                ->where('anonimity_reports.isDeleted','=','0')
				->get();

                foreach($reports as $rep)
                {
                    $rep['resFullName']= $rep->responderFirstName." ".$rep->responderLastName;
                    $rep['stuFullName']= $rep->studentFirstName." ".$rep->studentLastName;
                }
                
                if ($reports) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result']=base64_encode(json_encode($reports));
                    $response['status'] = true;
                }  
            }
        }
        return $response;
    }

    public function getAnonimityRequestT()
    {
        // $user = JWTAuth::toUser($request->token);
        // $response = [
        //         'data' => [
        //             'code'      => 400,
        //             'error'     => '',
        //             'message'   => 'Invalid Token! User Not Found.',
        //         ],
        //         'status' => false
        //     ];
        // if(!empty($user))
        {
            // $response = [
            //     'data' => [
            //         'code' => 400,
            //         'message' => 'Something went wrong. Please try again later!',
            //     ],
            //    'status' => false
            // ];
            // $rules = [
            //     'schoolProfileId' => 'required'
            // ];
            // $validator = Validator::make($request->all(), $rules);
            // if ($validator->fails()) {
            //     $response['data']['message'] = 'Invalid input values.';
            //     $response['data']['errors'] = $validator->messages();
            // }else
            {
                $schoolProfile = SchoolProfiles::find(31);

                $reports = Anonimity::
                join('responder_profiles as responder', 'responder.userId','=','anonimity_reports.responderId')
                ->join('student_profiles as student', 'student.userId','=','anonimity_reports.studentId')
                ->join('user_threads as thread', 'thread.id','=','anonimity_reports.threadId')
                ->select('anonimity_reports.id',
                         'anonimity_reports.status',
                         'anonimity_reports.message',
                         'anonimity_reports.comment',
                         'anonimity_reports.threadId',
                         'anonimity_reports.createdAt as createdAtUTC',
                         'responder.firstName as responderFirstName',
                         'responder.lastName as responderLastName',
                         'student.firstName as studentFirstName',
                         'student.lastName as studentLastName',
                         'student.studentId',
                         'thread.causeData'
                        )
                //->where('anonimity_reports.schoolAdminProfileId','=',$user->schoolAdminProfile->id)
                ->where('anonimity_reports.schoolProfileId','=',31)
                ->orderBy('anonimity_reports.id', 'desc')               
                //->where('status','=','0')
                ->get();

                foreach($reports as $rep)
                {
                    $rep['resFullName']= $rep->responderFirstName." ".$rep->responderLastName;
                    $rep['stuFullName']= $rep->studentFirstName." ".$rep->studentLastName;

                    $schoolTimeZone   = $schoolProfile->schoolTimeZone;
                    $rep['createdAt'] = strtotime($rep->createdAtUTC.$schoolTimeZone);

                    return $rep;
                    //$rep['createdAt'] = date('H:i',$rep->createdAt);
                    
                }
                
                if ($reports) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result']=$reports;
                    $response['status'] = true;
                }  
            }
        }
        return $response;
    }

    public function delAnonimityRequest(Request $request)
    {
        $request['id'] = (int)base64_decode($request->id);
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
            	'id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
            	$reports = Anonimity::find($request->id);
                
                if ($reports->delete()) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                }  
            }
        }
        return $response;
    }

    public function approveAnonimityRequest(Request $request)
    {
        $request['id'] = (int)base64_decode($request->id);
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
            	'id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
            	$reports = Anonimity::find($request->id);
                
                $thread = Threads::find($reports->threadId);



                $reports->status=1;
                $thread->anonimityFlag=0;

                if ($reports->save() && $thread->save()) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                }  
            }
        }
        return $response;
    }

    public function sendAnonimityRequest(Request $requestA)
    {
        $request = $requestA;
        $request['message'] = base64_decode($requestA->message);
        $request['comment'] = base64_decode($requestA->comment);
        $request['threadId'] = (int)base64_decode($requestA->threadId);
        $request['responderId'] = (int)base64_decode($requestA->responderId);
        $request['studentId'] = (int)base64_decode($requestA->studentId);

    	$user = JWTAuth::toUser($requestA->token);
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
               'responderId'  	=> ['required','exists:users,id'],
               'studentId'    	=> ['required','exists:users,id'],
               'message'		=> 'required',
               'comment'		=> 'required',
               'threadId'		=> 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
            	$responder = Responder::where('userId','=',$request->get('responderId'))->first();
                $schoolTimeZone = $responder->schoolProfile->schoolTimeZone;

                $localCreatedAt=Carbon::now()->format('Y-m-d H:i:s');
              	$anonimity_report = Anonimity::create([
                    'responderId'     		=> $request->get('responderId'),
                    'studentId'       		=> $request->get('studentId'),
                    'message'       		=> $request->get('message'),
                    'comment'       		=> $request->get('comment'),
                    'threadId'       		=> $request->get('threadId'),
                    'schoolProfileId'	    => $responder->schoolProfileId,
                    'localCreatedAt'        => date('Y-m-d H:i:s',strtotime($localCreatedAt.$schoolTimeZone." Hours"))
                ]); 


                $thread = Threads::find($request->get('threadId'));
                $thread->anonimityFlag=0;

                if ($thread->save() && $anonimity_report) 
                {
                    if($anonimity_report->user->Responder->schoolProfile->schoolSecondaryAdminProfile!=null)
                    {
                        $secAdmin = $anonimity_report->user->Responder->schoolProfile->schoolSecondaryAdminProfile->user->username;
                    }
                    else
                    {
                        $secAdmin = "None";
                    }
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result']['AdminUserName'] = $anonimity_report->user->Responder->schoolProfile->schoolAdminProfile->user->username;
                    $response['data']['result']['SecAdminUserName'] = $secAdmin;
                    $response['data']['result']['message'] = "A new Reported Message Came";
                    $response['status'] = true;
                    $response['data']['result'] = base64_encode(json_encode($response['data']['result']));
                }  
                
                // if ($anonimity_report) 
                // {
                //     $response['data']['message'] = 'Request Successfull';
                //     $response['data']['code'] = 200;
                //     $response['status'] = true;
                // }  
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

                $reported=array();
                $reported[0] = Anonimity::where('schoolProfileId','=',$request->schoolProfileId)
                                        ->whereDate('localCreatedAt', '=', $date[0])
                                        ->count();

                for($i=1;$i<=6;$i++)
                {
                    $reported[$i] =Anonimity::where('schoolProfileId','=',$request->schoolProfileId)
                                        ->whereDate('localCreatedAt', '=', $date[$i])
                                        ->count();
                }                  
                
                if ($reported) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result'] = $reported;
                }  
            }
        }
        return $response;
    }

    public function markReportedRead(Request $request)
    {
        $request['id'] = (int)base64_decode($request->id);

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
               //'schoolProfileId'    => ['required','exists:school_profiles,id'],
               'id' =>  ['required','exists:anonimity_reports,id'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $markRead = Anonimity::find($request->id);                
                $markRead->status =1;
                $markRead->save();

                if ($markRead) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    //$response['data']['result'] = $reported;
                }  
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
                $filterText="Today";
                if($filterType=="Weekly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $SD=Carbon::parse($dateLocalS)->startOfWeek()->format('Y-m-d');
                    $ED=Carbon::parse($SD)->addDay(6)->format('Y-m-d');

                    $count = Anonimity::where('schoolProfileId','=',$school->id)
                                        ->whereDate('localCreatedAt','>=',$SD)
                                        ->whereDate('localCreatedAt','<=',$ED)
                                        ->count();
                    $filterText="This Week";
                }
                elseif($filterType == "Yearly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $SD=Carbon::parse($dateLocalS)->startOfYear()->format('Y-m-d');
                    $ED=Carbon::parse($SD)->addDay(364)->format('Y-m-d');

                    $count = Anonimity::where('schoolProfileId','=',$school->id)
                                        ->whereDate('localCreatedAt','>=',$SD)
                                        ->whereDate('localCreatedAt','<=',$ED)
                                        ->count();
                    $filterText="This Year";

                }
                elseif($filterType == "Monthly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $SD=Carbon::parse($dateLocalS)->startOfMonth()->format('Y-m-d');
                    $numberOfDays = Carbon::parse($SD)->daysInMonth;
                    $ED=Carbon::parse($SD)->addDay($numberOfDays-1)->format('Y-m-d');

                    $count = Anonimity::where('schoolProfileId','=',$school->id)
                                        ->whereDate('localCreatedAt','>=',$SD)
                                        ->whereDate('localCreatedAt','<=',$ED)
                                        ->count();
                    $filterText="This Month";

                }
                elseif($filterType == "Today")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $SD=Carbon::parse($dateLocalS)->format('Y-m-d');

                    $count = Anonimity::where('schoolProfileId','=',$school->id)
                                        ->whereDate('localCreatedAt','=',$SD)
                                        ->count();
                    $filterText="Today";
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
