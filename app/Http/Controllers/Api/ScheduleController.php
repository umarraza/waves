<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use JWTAuthException;
use JWTAuth;
use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiScheduleSessions as ScheduleSessions;
use App\Models\Api\ApiScheduleSessions2 as ScheduleSessions2;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;
use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiStudent as Student;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // Create New Schedule For Messages
    public function createSession(Request $request)
    {
        $request['userId'] = (int)base64_decode($request->userId);
        $request['startDateTime'] = base64_decode($request->startDateTime);
        $request['endDateTime'] = base64_decode($request->endDateTime);
        $request['type'] = (int)base64_decode($request->type);
        $request['repeated'] = (int)base64_decode($request->repeated);
        $request['rEndDate'] = base64_decode($request->rEndDate);
        $request['description'] = base64_decode($request->description);
        $request['causeData'] = base64_decode($request->causeData);
        
        $user = JWTAuth::toUser($request->token);
        $response = [
              'data' => [
                  'code'      => 400,
                  'errors'     => '115',
                  'message'   => 'Invalid Token! User Not Found.',
              ],
              'status' => false
          ];
        if(!empty($user) && $user->isResponder() && $user->statusVerified())
        {

            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
                'status' => false
            ];
            $rules = [
                'userId'        => ['required', 'exists:users,id,roleId,4'],
                'startDateTime' => ['required'],// 'date_format:Y-m-d H:i'],//,'after:now'],
                'endDateTime'   => ['required'],//'date_format:Y-m-d H:i','after:startDateTime'],
                'type'          => ['required','in:'.implode(",",array_keys(ScheduleSessions2::getTypes()))],
                'repeated'      => 'required_unless:type,"'.ScheduleSessions2::TYPE_NEVER.'"|in:'.ScheduleSessions2::NOT_REPEATED.','.ScheduleSessions2::REPEATED,
                'rEndDate'      =>['after:endDateTime'],//'date_format:Y-m-d H:i',
                'description'   => ['required','max:255'],
                'causeData'     => ['required','json']
            ];
            $validator = Validator::make($request->all(), $rules);
            $validator->sometimes('rEndDate', 'required', function ($input) {
                return $input->type != ScheduleSessions2::TYPE_NEVER && $input->repeated == ScheduleSessions2::NOT_REPEATED;
            });

            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $response['status'] = true;
                $response['data']['code'] = 501;
                $response['data']['message'] = 'Invalid user id!';
                $modelStudent = Student::where('userId', $request->get('userId'))->first();
                $startDate  = date('Y-m-d H:i:s',strtotime($request->get('startDateTime')));
                $endDate    = date('Y-m-d H:i:s',strtotime($request->get('endDateTime')));
                $rEndDate   = date('Y-m-d H:i:s',strtotime($request->get('rEndDate')));

                $onlySD  = Carbon::parse($request->startDateTime)->setTime('0','0','0','0');
                $onlyRED = Carbon::parse($request->rEndDate)->setTime('0','0','0','0');
                if(!empty($modelStudent))
                {
                    $modelStudentResponder = StudentResponder::where('studentProfileId', $modelStudent->id)
                        ->where('responderProfileId', $user->responder->id)
                        ->first();

                    $response['data']['message'] = 'Invalid user! This student is not assigned to you.';
                    if(!empty($modelStudentResponder))
                    {
                        if($request->type == ScheduleSessions2::TYPE_DAILY)
                        {
                            $difference = $onlySD->diffInDays($onlyRED);

                            $SD = Carbon::parse($request->startDateTime)->subDay();//->addMinutes(5);
                            $ED = Carbon::parse($request->endDateTime)->subDay();//->addMinutes(5);
                            
                            $dataValues = [];
                            $uniqueSessionId = $user->id.time();
                            for($i=0;$i<=$difference;$i++)
                            {
                                $SD = $SD->addDay();
                                $ED = $ED->addDay();
                                $dataValues[] = [
                                    'studentProfileId'      => $modelStudentResponder->studentProfileId,
                                    'responderProfileId'    => $modelStudentResponder->responderProfileId,
                                    'type'                  => $request->type,
                                    'repeated'              => $request->repeated,
                                    'description'           => $request->description,
                                    'status'                => ScheduleSessions2::ACTIVE,
                                    'startDate'             => $SD->format('Y-m-d H:i:s'),
                                    'endDate'               => $ED->format('Y-m-d H:i:s'),
                                    'rEndDate'              => $rEndDate,
                                    'sessionId'             => $uniqueSessionId,
                                    'causeData'             => json_encode(json_decode($request->causeData)),
                                    'schoolProfileId'       => $user->Responder->schoolProfileId,
                                    'isDeleted'             =>  0,
                                                ];
                            }
                        }
                        elseif($request->type == ScheduleSessions2::TYPE_WEEKLY)
                        {
                            //return $onlyRED;
                            //$difference = $onlySD->diffInDays($onlyRED);
                            //return intval($difference/7);

                            $difference = intval(($onlySD->diffInDays($onlyRED))/7);

                            $SD = Carbon::parse($request->startDateTime)->subWeek();//->addMinutes(5);
                            $ED = Carbon::parse($request->endDateTime)->subWeek();//->addMinutes(5);
                            
                            $dataValues = [];
                            $uniqueSessionId = $user->id.time();
                            for($i=0;$i<=$difference;$i++)
                            {
                                $SD = $SD->addWeek();
                                $ED = $ED->addWeek();
                                $dataValues[] = [
                                    'studentProfileId'      => $modelStudentResponder->studentProfileId,
                                    'responderProfileId'    => $modelStudentResponder->responderProfileId,
                                    'type'                  => $request->type,
                                    'repeated'              => $request->repeated,
                                    'description'           => $request->description,
                                    'status'                => ScheduleSessions2::ACTIVE,
                                    'startDate'             => $SD->format('Y-m-d H:i:s'),
                                    'endDate'               => $ED->format('Y-m-d H:i:s'),
                                    'rEndDate'              => $rEndDate,
                                    'sessionId'             => $uniqueSessionId,
                                    'causeData'             => json_encode(json_decode($request->causeData)),
                                    'schoolProfileId'       => $user->Responder->schoolProfileId,
                                    'isDeleted'             =>  0,
                                                ];
                            }
                        }
                        elseif($request->type == ScheduleSessions2::TYPE_MONTHLY)
                        {
                            $difference = $onlySD->diffInMonths($onlyRED);

                            $SD = Carbon::parse($request->startDateTime)->subMonth();//->addMinutes(5);
                            $ED = Carbon::parse($request->endDateTime)->subMonth();//->addMinutes(5);
                            
                            $dataValues = [];
                            $uniqueSessionId = $user->id.time();
                            for($i=0;$i<=$difference;$i++)
                            {
                                $SD = $SD->addMonth();
                                $ED = $ED->addMonth();
                                $dataValues[] = [
                                    'studentProfileId'      => $modelStudentResponder->studentProfileId,
                                    'responderProfileId'    => $modelStudentResponder->responderProfileId,
                                    'type'                  => $request->type,
                                    'repeated'              => $request->repeated,
                                    'description'           => $request->description,
                                    'status'                => ScheduleSessions2::ACTIVE,
                                    'startDate'             => $SD->format('Y-m-d H:i:s'),
                                    'endDate'               => $ED->format('Y-m-d H:i:s'),
                                    'rEndDate'              => $rEndDate,
                                    'sessionId'             => $uniqueSessionId,
                                    'causeData'             => json_encode(json_decode($request->causeData)),
                                    'schoolProfileId'       => $user->Responder->schoolProfileId,
                                    'isDeleted'             =>  0,
                                                ];
                            }   
                        }
                        else
                        {

                            $SD = Carbon::parse($request->startDateTime);//->addMinutes(5);
                            $ED = Carbon::parse($request->endDateTime);//->addMinutes(5);
                            
                            $dataValues = [];
                            $uniqueSessionId = $user->id.time();
                            for($i=0;$i<1;$i++)
                            {
                                $dataValues[] = [
                                    'studentProfileId'      => $modelStudentResponder->studentProfileId,
                                    'responderProfileId'    => $modelStudentResponder->responderProfileId,
                                    'type'                  => $request->type,
                                    'repeated'              => $request->repeated,
                                    'description'           => $request->description,
                                    'status'                => ScheduleSessions2::ACTIVE,
                                    'startDate'             => $SD->format('Y-m-d H:i:s'),
                                    'endDate'               => $ED->format('Y-m-d H:i:s'),
                                    'rEndDate'              => $rEndDate,
                                    'sessionId'             => $uniqueSessionId,
                                    'causeData'             => json_encode(json_decode($request->causeData)),
                                    'schoolProfileId'       => $user->Responder->schoolProfileId,
                                    'isDeleted'             =>  0,
                                                ];
                            }
                        }
                        if(!$modelStudentResponder->checkBothUser2($dataValues))
                        {

                            $modelSchedule = DB::table('schedule_sessions2')->insert($dataValues);
                            if($modelSchedule){

                                $repeatedTypeEmail = "Never";
                                if($request->type==0)
                                {
                                    $repeatedTypeEmail="Never";
                                }
                                elseif($request->type==1)
                                {
                                    $repeatedTypeEmail="Daily";
                                }
                                elseif($request->type==2)
                                {
                                    $repeatedTypeEmail="Weekly";
                                }
                                elseif($request->type==3)
                                {
                                    $repeatedTypeEmail="Monthly";
                                }

                                

                                //////////////////////////////////////////////////////////
                                $schoolTimeZone = $user->responder->schoolProfile->schoolTimeZone;
                                $message = 'New session scheduled by '.$user->getName().' at '.date('Y-m-d g:i A',strtotime($startDate.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($endDate.$schoolTimeZone." Hours"));
                                $modelStudent->user->sendPushNotification($message,"schedule");

                                
                                $messageData = [
                                    'message'   => 'You have a new session scheduled.',
                                    'name'      => $user->responder->title." ".$user->responder->lastName,
                                    'date'      => date('Y-m-d',strtotime($startDate.$schoolTimeZone." Hours")),
                                    'time'      =>  date('g:i A',strtotime($startDate.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($endDate.$schoolTimeZone." Hours")),
                                    'to'        => 'student',
                                    'repeated'  => $repeatedTypeEmail,
                                    'googleLink' => "",
                                    'outlookLink' => ""
                                  ];

                                $linkStartDate = date('Ymd',strtotime($startDate.$schoolTimeZone." Hours"));
                                $linkStartTime = date('Hi',strtotime($startDate.$schoolTimeZone." Hours"));
                                $linkEndDate = date('Ymd',strtotime($endDate.$schoolTimeZone." Hours"));
                                $linkEndTime = date('Hi',strtotime($endDate.$schoolTimeZone." Hours"));
                                $messageData['googleLink'] = "https://www.google.com/calendar/render?action=TEMPLATE&text=Session&dates=".$linkStartDate."T".$linkStartTime."00/".$linkEndDate."T".$linkEndTime."00&details=You+have,+a+session+with+".$user->responder->title."+".$user->responder->lastName."&sf=true&output=xml";
                                $fileName = "Session_S".$user->id."_".time().".ics";
                                \Storage::put($fileName, "BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTAMP:".$linkStartDate."T".$linkStartTime."00"."
STATUS:CONFIRMED
UID:1547873062addeventcom
DTSTART:".$linkStartDate."T".$linkStartTime."00"."
DTEND:".$linkEndDate."T".$linkEndTime."00"."
SUMMARY:Session
DESCRIPTION:You have a session with ".$user->responder->title." ".$user->responder->lastName.".
BEGIN:VALARM
TRIGGER:-PT15M
ACTION:DISPLAY
END:VALARM
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR");

                                $messageData['outlookLink'] = "http://18.191.77.130/wavesbackend/storage/app/".$fileName;


                                $modelStudent->user->sendSessionMail($messageData,$modelStudent->firstName,"The Waves App | Session Scheduled");

                                
                                //////////////////////////////////////////////////////////
                                $messageData['name'] = $modelStudent->firstName." ".$modelStudent->lastName;
                                
                                $messageData['googleLink'] = "https://www.google.com/calendar/render?action=TEMPLATE&text=Session&dates=".$linkStartDate."T".$linkStartTime."00/".$linkEndDate."T".$linkEndTime."00&details=You+have,+a+session+with+".$modelStudent->firstName."+".$modelStudent->lastName."&sf=true&output=xml";
                                $fileName = "Session_R".$user->id."_".time().".ics";
                                \Storage::put($fileName, "BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTAMP:".$linkStartDate."T".$linkStartTime."00"."
STATUS:CONFIRMED
UID:1547873062addeventcom
DTSTART:".$linkStartDate."T".$linkStartTime."00"."
DTEND:".$linkEndDate."T".$linkEndTime."00"."
SUMMARY:Session
DESCRIPTION:You have a session with ".$messageData['name'].".
BEGIN:VALARM
TRIGGER:-PT15M
ACTION:DISPLAY
END:VALARM
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR");

                                $messageData['outlookLink'] = "http://18.191.77.130/wavesbackend/storage/app/".$fileName;
                                $messageData['to'] = 'responder';

                                $user->sendSessionMail($messageData,$user->responder->firstName,"The Waves App | Session Scheduled");


                                //////////////////////////////////////////////////////////

                                $response['data']['code'] = 200;
                                $response['data']['message'] = 'Request Successfull.';

                                $modelSchedule = ScheduleSessions2::where('sessionId','=',$uniqueSessionId)->first();
                                $modelSchedule->description = $message;
                                $response['data']['result'] = base64_encode(json_encode($modelSchedule->getArrayResponse()));
                            }
                        }
                        else
                        {
                            $response['data']['message'] = 'Unfortunately, this slot is not available. Please select another date/time';
                            $response['data']['code'] = 502;
                        }
                    }
                } 
            }
        }
      return $response;
    }

    public function updateSession(Request $request)
    {
        $request['scheduleId'] = (int)base64_decode($request->scheduleId);
        $request['startDateTime'] = base64_decode($request->startDateTime);
        $request['endDateTime'] = base64_decode($request->endDateTime);
        $request['type'] = (int)base64_decode($request->type);
        $request['repeated'] = (int)base64_decode($request->repeated);
        $request['rEndDate'] = base64_decode($request->rEndDate);
        $request['description'] = base64_decode($request->description);
        $request['causeData'] = base64_decode($request->causeData);
        
        $user = JWTAuth::toUser($request->token);
        $response = [
              'data' => [
                  'code'      => 400,
                  'errors'     => '',
                  'message'   => 'Invalid Token! User Not Found.',
              ],
              'status' => false
          ];
        if(!empty($user) && $user->isResponder() && $user->statusVerified())
        {

            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
                'status' => false
            ];
            $rules = [
                'scheduleId'        => ['required', 'exists:schedule_sessions2,id'],
                'startDateTime' => ['required'],//, 'date_format:Y-m-d H:i'],//,'after:now'],
                'endDateTime'   => ['required'],//,'date_format:Y-m-d H:i','after:startDateTime'],
                'type'          => ['required','in:'.implode(",",array_keys(ScheduleSessions2::getTypes()))],
                'repeated'      => 'required_unless:type,"'.ScheduleSessions2::TYPE_NEVER.'"|in:'.ScheduleSessions2::NOT_REPEATED.','.ScheduleSessions2::REPEATED,
                'rEndDate'      => ['required'],//['date_format:Y-m-d H:i','after:endDateTime'],
                'description'   => ['required','max:255'],
                'causeData'     => ['required','json']
            ];
            $validator = Validator::make($request->all(), $rules);
            $validator->sometimes('rEndDate', 'required', function ($input) {
                return $input->type != ScheduleSessions2::TYPE_NEVER && $input->repeated == ScheduleSessions2::NOT_REPEATED;
            });

            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {

                $response['status'] = true;
                $response['data']['code'] = 501;
                $response['data']['message'] = 'Invalid user id!';
                $modelSchedule  = ScheduleSessions2::where('id', $request->get('scheduleId'))->first();
                $startDate  = date('Y-m-d H:i:s',strtotime($request->get('startDateTime')));
                $endDate    = date('Y-m-d H:i:s',strtotime($request->get('endDateTime')));
                $rEndDate   = date('Y-m-d H:i:s',strtotime($request->get('rEndDate')));

                $onlySD  = Carbon::parse($request->startDateTime)->setTime('0','0','0','0');
                $onlyRED = Carbon::parse($request->rEndDate)->setTime('0','0','0','0');
                if(!empty($modelSchedule))
                {
                    $modelStudentResponder = StudentResponder::where('studentProfileId', $modelSchedule->studentProfileId)
                        ->where('responderProfileId', $user->responder->id)
                        ->first();

                    $response['data']['message'] = 'Invalid user! This student is not assigned to you.';
                    if(!empty($modelStudentResponder))
                    {
                        if($request->type == ScheduleSessions2::TYPE_DAILY)
                        {
                            $difference = $onlySD->diffInDays($onlyRED);

                            $SD = Carbon::parse($request->startDateTime)->subDay();
                            $ED = Carbon::parse($request->endDateTime)->subDay();
                            
                            $dataValues = [];
                            $uniqueSessionId = $user->id.time();
                            for($i=0;$i<=$difference;$i++)
                            {
                                $SD = $SD->addDay();
                                $ED = $ED->addDay();
                                $dataValues[] = [
                                    'studentProfileId'      => $modelStudentResponder->studentProfileId,
                                    'responderProfileId'    => $modelStudentResponder->responderProfileId,
                                    'type'                  => $request->type,
                                    'repeated'              => $request->repeated,
                                    'description'           => $request->description,
                                    'status'                => ScheduleSessions2::ACTIVE,
                                    'startDate'             => $SD->format('Y-m-d H:i:s'),
                                    'endDate'               => $ED->format('Y-m-d H:i:s'),
                                    'rEndDate'              => $rEndDate,
                                    'sessionId'             => $uniqueSessionId,
                                    'causeData'             => json_encode(json_decode($request->causeData)),
                                    'schoolProfileId'       => $user->Responder->schoolProfileId,
                                    'isDeleted'             =>  0,
                                                ];
                            }
                        }
                        elseif($request->type == ScheduleSessions2::TYPE_WEEKLY)
                        {
                            //return $onlyRED;
                            //$difference = $onlySD->diffInDays($onlyRED);
                            //return intval($difference/7);

                            $difference = intval(($onlySD->diffInDays($onlyRED))/7);


                            //return $difference;

                            $SD = Carbon::parse($request->startDateTime)->subWeek();
                            $ED = Carbon::parse($request->endDateTime)->subWeek();
                            
                            $dataValues = [];
                            $uniqueSessionId = $user->id.time();
                            for($i=0;$i<=$difference;$i++)
                            {
                                $SD = $SD->addWeek();
                                $ED = $ED->addWeek();
                                $dataValues[] = [
                                    'studentProfileId'      => $modelStudentResponder->studentProfileId,
                                    'responderProfileId'    => $modelStudentResponder->responderProfileId,
                                    'type'                  => $request->type,
                                    'repeated'              => $request->repeated,
                                    'description'           => $request->description,
                                    'status'                => ScheduleSessions2::ACTIVE,
                                    'startDate'             => $SD->format('Y-m-d H:i:s'),
                                    'endDate'               => $ED->format('Y-m-d H:i:s'),
                                    'rEndDate'              => $rEndDate,
                                    'sessionId'             => $uniqueSessionId,
                                    'causeData'             => json_encode(json_decode($request->causeData)),
                                    'schoolProfileId'       => $user->Responder->schoolProfileId,
                                    'isDeleted'             =>  0,
                                                ];
                            }
                        }
                        elseif($request->type == ScheduleSessions2::TYPE_MONTHLY)
                        {
                            $difference = $onlySD->diffInMonths($onlyRED);

                            $SD = Carbon::parse($request->startDateTime)->subMonth();
                            $ED = Carbon::parse($request->endDateTime)->subMonth();
                            
                            $dataValues = [];
                            $uniqueSessionId = $user->id.time();
                            for($i=0;$i<=$difference;$i++)
                            {
                                $SD = $SD->addMonth();
                                $ED = $ED->addMonth();
                                $dataValues[] = [
                                    'studentProfileId'      => $modelStudentResponder->studentProfileId,
                                    'responderProfileId'    => $modelStudentResponder->responderProfileId,
                                    'type'                  => $request->type,
                                    'repeated'              => $request->repeated,
                                    'description'           => $request->description,
                                    'status'                => ScheduleSessions2::ACTIVE,
                                    'startDate'             => $SD->format('Y-m-d H:i:s'),
                                    'endDate'               => $ED->format('Y-m-d H:i:s'),
                                    'rEndDate'              => $rEndDate,
                                    'sessionId'             => $uniqueSessionId,
                                    'causeData'             => json_encode(json_decode($request->causeData)),
                                    'schoolProfileId'       => $user->Responder->schoolProfileId,
                                    'isDeleted'             =>  0,
                                                ];
                            }   
                        }
                        else
                        {

                            $SD = Carbon::parse($request->startDateTime);
                            $ED = Carbon::parse($request->endDateTime);
                            
                            $dataValues = [];
                            $uniqueSessionId = $user->id.time();
                            for($i=0;$i<1;$i++)
                            {
                                $dataValues[] = [
                                    'studentProfileId'      => $modelStudentResponder->studentProfileId,
                                    'responderProfileId'    => $modelStudentResponder->responderProfileId,
                                    'type'                  => $request->type,
                                    'repeated'              => $request->repeated,
                                    'description'           => $request->description,
                                    'status'                => ScheduleSessions2::ACTIVE,
                                    'startDate'             => $SD->format('Y-m-d H:i:s'),
                                    'endDate'               => $ED->format('Y-m-d H:i:s'),
                                    'rEndDate'              => $rEndDate,
                                    'sessionId'             => $uniqueSessionId,
                                    'causeData'             => json_encode(json_decode($request->causeData)),
                                    'schoolProfileId'       => $user->Responder->schoolProfileId,
                                    'isDeleted'             =>  0,
                                                ];
                            }
                        }
                        $oldModelSchedule = ScheduleSessions2::find($request->scheduleId);
                        if(!$modelStudentResponder->checkBothUserU2($dataValues,$oldModelSchedule->sessionId) && $oldModelSchedule)
                        {
                            $currentDate        = date('Y-m-d H:i:s');
                            $modelSchedule      = DB::table('schedule_sessions2')->insert($dataValues);
                            $removeOldSchedule  = ScheduleSessions2::where('endDate','>=',$currentDate)
                                                                    ->where('sessionId','=',$oldModelSchedule->sessionId)
                                                                    ->delete();
                            if($modelSchedule && $removeOldSchedule )
                            {    
                                $modelStudent = Student::find($modelStudentResponder->studentProfileId);
                                $schoolTimeZone = $user->responder->schoolProfile->schoolTimeZone;
                                
                                $message = "Your session with ".$user->getName()." at ".date('Y-m-d g:i A',strtotime($oldModelSchedule->startDate.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($oldModelSchedule->endDate.$schoolTimeZone." Hours")).' has been rescheduled to '.date('Y-m-d g:i A',strtotime($request->startDateTime.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($request->endDateTime.$schoolTimeZone." Hours"));

                                // $message = date('Y-m-d g:i A',strtotime($oldModelSchedule->startDate.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($oldModelSchedule->endDate.$schoolTimeZone." Hours")).' has been edited '.date('Y-m-d g:i A',strtotime($request->startDateTime.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($request->endDateTime.$schoolTimeZone." Hours")).' by '.$user->getName();

                                //$message = 'New session scheduled by '.$user->getName().' '.date('Y-m-d H:i',strtotime($startDate)).' - '.date('H:i',strtotime($endDate));
                                $modelStudent->user->sendPushNotification($message,"schedule");

                                $repeatedTypeEmail = "Never";
                                if($request->type==0)
                                {
                                    $repeatedTypeEmail="Never";
                                }
                                elseif($request->type==1)
                                {
                                    $repeatedTypeEmail="Daily";
                                }
                                elseif($request->type==2)
                                {
                                    $repeatedTypeEmail="Weekly";
                                }
                                elseif($request->type==3)
                                {
                                    $repeatedTypeEmail="Monthly";
                                }
                                
                                $messageData = [
                                    'message'   => 'One of your sessions have been updated.',
                                    'name'      => $user->responder->title." ".$user->responder->lastName,
                                    'date'      => date('Y-m-d',strtotime($startDate.$schoolTimeZone." Hours")),
                                    'time'      =>  date('g:i A',strtotime($startDate.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($endDate.$schoolTimeZone." Hours")),
                                    'to'        => 'student',
                                    'repeated'  => $repeatedTypeEmail,
                                    'googleLink' => "",
                                    'outlookLink' => ""
                                  ];

                                $linkStartDate = date('Ymd',strtotime($startDate.$schoolTimeZone." Hours"));
                                $linkStartTime = date('Hi',strtotime($startDate.$schoolTimeZone." Hours"));
                                $linkEndDate = date('Ymd',strtotime($endDate.$schoolTimeZone." Hours"));
                                $linkEndTime = date('Hi',strtotime($endDate.$schoolTimeZone." Hours"));

                                $messageData['googleLink'] = "https://www.google.com/calendar/render?action=TEMPLATE&text=Session&dates=".$linkStartDate."T".$linkStartTime."00/".$linkEndDate."T".$linkEndTime."00&details=You+have,+a+session+with+".$user->responder->title."+".$user->responder->lastName."&sf=true&output=xml";
                                $fileName = "Session_S".$user->id."_".time().".ics";
                                \Storage::put($fileName, "BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTAMP:".$linkStartDate."T".$linkStartTime."00"."
STATUS:CONFIRMED
UID:1547873062addeventcom
DTSTART:".$linkStartDate."T".$linkStartTime."00"."
DTEND:".$linkEndDate."T".$linkEndTime."00"."
SUMMARY:Session
DESCRIPTION:You have a session with ".$user->responder->title." ".$user->responder->lastName.".
BEGIN:VALARM
TRIGGER:-PT15M
ACTION:DISPLAY
END:VALARM
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR");

                                $messageData['outlookLink'] = "http://18.191.77.130/wavesbackend/storage/app/".$fileName;

                                $modelStudent->user->sendSessionMail($messageData,$modelStudent->firstName,"The Waves App | Updated Session");

                                
                                //////////////////////////////////////////////////////////
                                $messageData['googleLink'] = "https://www.google.com/calendar/render?action=TEMPLATE&text=Session&dates=".$linkStartDate."T".$linkStartTime."00/".$linkEndDate."T".$linkEndTime."00&details=You+have,+a+session+with+".$modelStudent->firstName."+".$modelStudent->lastName."&sf=true&output=xml";

                                $messageData['name'] = $modelStudent->firstName." ".$modelStudent->lastName;
                                $messageData['to'] = 'responder';
                                $fileName = "Session_R".$user->id."_".time().".ics";
                                \Storage::put($fileName, "BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTAMP:".$linkStartDate."T".$linkStartTime."00"."
STATUS:CONFIRMED
UID:1547873062addeventcom
DTSTART:".$linkStartDate."T".$linkStartTime."00"."
DTEND:".$linkEndDate."T".$linkEndTime."00"."
SUMMARY:Session
DESCRIPTION:You have a session with ".$messageData['name'].".
BEGIN:VALARM
TRIGGER:-PT15M
ACTION:DISPLAY
END:VALARM
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR");

                                $messageData['outlookLink'] = "http://18.191.77.130/wavesbackend/storage/app/".$fileName;

                                $user->sendSessionMail($messageData,$user->responder->firstName,"The Waves App | Updated Session");
                                //////////////////////////////////////////////////////////

                                $response['data']['code'] = 200;
                                $response['data']['message'] = 'Request Successfull.';

                                $modelSchedule = ScheduleSessions2::where('sessionId','=',$uniqueSessionId)->first();
                                $modelSchedule->description = $message;
                                $response['data']['result'] = base64_encode(json_encode($modelSchedule->getArrayResponse()));
                            }
                        }
                        else
                        {
                            $response['data']['message'] = 'Unfortunately, this slot is not available. Please select another date/time';
                            $response['data']['code'] = 502;
                        }
                    }
                } 
            }
        }
      return $response;
    }

    

    // Return Schedule List
    public function listSession(Request $request)
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
        if(!empty($user) && ($user->isResponder() || $user->isStudent()) && $user->statusVerified())
        {
            $response['data']['message']    = 'Request Successfull.';
            $response['data']['code']       = 200;
            $response['data']['result']     = [];
            $response['status']             = true;

            $attrId = 'responderProfileId';
            $typeId = '';
            if($user->isStudent() && !empty($user->student)){
                $attrId = 'studentProfileId';
                $typeId = $user->student->id;
            }elseif(!empty($user->responder))
                $typeId = $user->responder->id;

            $currentDate = date('Y-m-d H:i:s');
            $currentTime = date('H:i:s');


            $modelScheduleUpcoming = ScheduleSessions2::where($attrId,'=',$typeId)
                                            ->where('endDate','>=',$currentDate)
                                            ->where('status','=',ScheduleSessions2::ACTIVE)
                                            ->where('isDeleted','=',0)
                                            ->orderBy('startDate','asc')
                                            ->get();
            
            $modelSchedulePrevious = ScheduleSessions2::where($attrId,'=',$typeId)
                                            ->where('endDate','<',$currentDate)
                                            ->where('status','=',ScheduleSessions2::ACTIVE)
                                            ->where('isDeleted','=',0)
                                            ->orderBy('startDate','asc')
                                            ->get();
            
            $randomString = array();
            $randomString[0] = "123";
            $index = 1;
            $response['data']['result']['upcoming'] = [];
            $response['data']['result']['previous'] = [];
            foreach ($modelScheduleUpcoming as $key => $value) 
            {
                $counter = 0;
                for($i=0;$i<count($randomString);$i++)
                {
                    if($randomString[$i]==$value->sessionId)
                    {
                        $counter++;
                    }    
                }
                if($counter == 0)
                {
                    $randomString[$index] =  $value->sessionId;
                    $index++;
                    $response['data']['result']['upcoming'][] = $value->getArrayResponse();
                }
                
            }
            foreach ($modelSchedulePrevious as $key => $value){
                $response['data']['result']['previous'][] = $value->getArrayResponse();
            }
            $response['data']['result'] = base64_encode(json_encode($response['data']['result']));
        }
        return $response;
    }

    

    // Delete Schedule Session
    public function deleteSession(Request $request)
    {
        $request['sessionId'] = (int)base64_decode($request->sessionId);

        $user = JWTAuth::toUser($request->token);
        $response = [
              'data' => [
                  'code'      => 400,
                  'errors'     => '',
                  'message'   => 'Invalid Token! User Not Found.',
              ],
              'status' => false
          ];
        if(!empty($user) && $user->isResponder() && $user->statusVerified())
        {
            $rules = [
                'sessionId'   => ['required','exists:schedule_sessions2,id']
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else{
                $response['data']['message']    = 'Session not deleted Successfully. Please try again later!';
                $response['data']['code']       = 500;
                $response['status']             = true;


                $getSession = ScheduleSessions2::find($request->sessionId);

                if(!empty($getSession))
                {
                    // $getSession = ScheduleSessions2::where('id','=',$request->sessionId)
                    //                             ->where('responderProfileId','=',$user->responder->id)
                    //                             ->first();
                    $student = Student::find($getSession->studentProfileId);
                    //return $student;
                    $currentDate = date('Y-m-d H:i:s');
                    $isDeleted = ScheduleSessions2::where('sessionId','=',$getSession->sessionId)
                                                ->where('endDate','>=',$currentDate)
                                                ->update(['isDeleted' => 1 ]);
                    if($isDeleted){
                        $schoolTimeZone = $user->responder->schoolProfile->schoolTimeZone;
                        $message = "Your session with ".$user->getName()." at ".date('Y-m-d g:i A',strtotime($getSession->startDate.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($getSession->endDate.$schoolTimeZone." Hours")).' has been cancelled.';
                        $student->user->sendPushNotification($message,"schedule");
                        

                        $repeatedTypeEmail = "Never";
                        if($getSession->type==0)
                        {
                            $repeatedTypeEmail="Never";
                        }
                        elseif($getSession->type==1)
                        {
                            $repeatedTypeEmail="Daily";
                        }
                        elseif($getSession->type==2)
                        {
                            $repeatedTypeEmail="Weekly";
                        }
                        elseif($getSession->type==3)
                        {
                            $repeatedTypeEmail="Monthly";
                        }
                        
                        $messageData = [
                            'message'   => 'One of your sessions has been cancelled.',
                            'name'      => $user->responder->title." ".$user->responder->lastName,
                            'date'      => date('Y-m-d',strtotime($getSession->startDate.$schoolTimeZone." Hours")),
                            'time'      =>  date('g:i A',strtotime($getSession->startDate.$schoolTimeZone." Hours")).' - '.date('g:i A',strtotime($getSession->endDate.$schoolTimeZone." Hours")),
                            'to'        => 'student',
                            'repeated'  => $repeatedTypeEmail,
                            'googleLink' => "No"
                          ];

                        $student->user->sendSessionMail($messageData,$student->firstName,"The Waves App | Session Cancelled");

                        
                        //////////////////////////////////////////////////////////
                        $messageData['name'] = $student->firstName." ".$student->lastName;
                        $messageData['to'] = 'responder';

                        $user->sendSessionMail($messageData,$user->responder->firstName,"The Waves App | Session Cancelled");
                        //////////////////////////////////////////////////////////

                        $getSession->description = $message;
                        $response['data']['result'] = base64_encode(json_encode($getSession->getArrayResponse()));
                        $response['data']['message']    = 'Request Successfully.';
                        $response['data']['code']       = 200;
                    }
                }
            }
        }
      return $response;
    }

    public function sentNotificationsAA()
    {
        $currentDate = date('Y-m-d H:i:s');
        $modelSchedules = ScheduleSessions2::where('endDate','>=',$currentDate)->get();
        
        foreach ($modelSchedules as $key => $value) 
        {

            $flag = 0;
            $flag2 = 0;

            $schoolTimeSymbol = $value->student->schoolProfile->schoolTimeSymbol;
            $schoolTimeValue  = $value->student->schoolProfile->schoolTimeValue;
            $schoolTimeZone   = $value->student->schoolProfile->schoolTimeZone." Hours";
            
            
            $plus1 =    $schoolTimeValue + 1;
            $plus12 =   $schoolTimeValue + 12;

            $currentDateTime = strtotime(date('Y-m-d H:i:s',strtotime($plus12.' Hours')));
            $currentDateTimeOneHour = strtotime(date('Y-m-d H:i:s',strtotime($plus1.' Hours')));
            


            $startDateTimeStamp = strtotime($value->startDate.$schoolTimeZone);
            
            //return date('H:i',$currentDateTimeOneHour)." ".date('H:i',$startDateTimeStamp);
            if(date('H:i',$currentDateTime) == date('H:i',$startDateTimeStamp)){
                 $flag = 1;
                 //return "flag 1 mai agea hai:";
            }
            if(date('H:i',$currentDateTimeOneHour) == date('H:i',$startDateTimeStamp)){
                $flag2 = 1;   
                //return "flag 2 mai agea hai:";
            }
            if($flag)
            {
                // Send to Student
                $message = 'Your upcoming session in next 12 hours with '.$value->responder->user->getName().' ('.$value->description.').';
                $value->student->user->sendPushNotification($message,"schedule");
                //Send to Responder
                $message = 'Your upcoming session in next 12 hours with '.$value->student->user->getName().' ('.$value->description.').';
                $value->responder->user->sendPushNotification($message,"schedule");
            }
            if($flag2)
            {
                //Send to Student
                $message = 'Your upcoming session in next 1 hours with '.$value->responder->user->getName().' ('.$value->description.').';
                $value->student->user->sendPushNotification($message,"schedule");
                //Send to Responder
                $message = 'Your upcoming session in next 1 hours with '.$value->student->user->getName().' ('.$value->description.').';
                $value->responder->user->sendPushNotification($message,"schedule");

                return $message;
            }
        }
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
        if(!empty($user))
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
                $oneDayBeforeWeek = Carbon::now()->startOfWeek()->subDay()->format('Y-m-d');
                $oneDayAfterWeek = Carbon::now()->endOfWeek()->addDay()->format('Y-m-d');
                $date[0]=Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');

                for($i=1; $i<=6 ; $i++ )
                {
                    $date[$i]=Carbon::parse($date[0])->addDay($i)->format('Y-m-d H:i:s');
                }

                for($i=0; $i<=6 ; $i++ )
                {
                    $date[$i]= date('Y-m-d',strtotime($date[$i].$schoolTimeZone." Hours"));
                }

                $schedules = ScheduleSessions2::whereDate('startDate','>=',$oneDayBeforeWeek)
                                                ->whereDate('startDate','<=',$oneDayAfterWeek)
                                                ->where('schoolProfileId','=',$request->schoolProfileId)
                                                ->get();
                foreach($schedules as $schedule)
                {
                    $schedule['startDate'] = date('Y-m-d',strtotime($schedule->startDate.$schoolTimeZone." Hours"));
                }


                $count=array();
                $count[0]=0;
                for($i=1; $i<=6 ; $i++ )
                {
                    $count[$i]=0;
                }

                foreach ($schedules as $schedule) {
                    if($date[0]==$schedule->startDate)
                    {
                        $count[0]++;
                    }
                    elseif($date[1]==$schedule->startDate)
                    {
                        $count[1]++;
                    }
                    elseif($date[2]==$schedule->startDate)
                    {
                        $count[2]++;
                    }
                    elseif($date[3]==$schedule->startDate)
                    {
                        $count[3]++;
                    }
                    elseif($date[4]==$schedule->startDate)
                    {
                        $count[4]++;
                    }
                    elseif($date[5]==$schedule->startDate)
                    {
                        $count[5]++;
                    }
                    elseif($date[6]==$schedule->startDate)
                    {
                        $count[6]++;
                    }
                }
                
                if ($schedules) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['data']['result'] = $count;
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
                $schedules="";
                $filterText="Today";
                if($filterType=="Weekly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $date=array();
                    $oneDayBeforeWeek = Carbon::parse($dateLocalS)->startOfWeek()->subDay()->format('Y-m-d');
                    $oneDayAfterWeek = Carbon::parse($dateLocalS)->endOfWeek()->addDay()->format('Y-m-d');

                    $SD=Carbon::parse($dateLocalS)->startOfWeek()->format('Y-m-d');
                    $ED=Carbon::parse($SD)->addDay(6)->format('Y-m-d');

                    $schedules = ScheduleSessions2::whereDate('startDate','>=',$oneDayBeforeWeek)
                                                    ->whereDate('startDate','<=',$oneDayAfterWeek)
                                                    ->where('schoolProfileId','=',$school->id)
                                                    ->get();

                    foreach($schedules as $schedule)
                    {
                        $schedule['startDate'] = date('Y-m-d',strtotime($schedule->startDate.$schoolTimeZone." Hours"));
                    }

                    foreach ($schedules as $schedule) 
                    {
                        
                        if(Carbon::parse($schedule->startDate)->greaterThanOrEqualTo(Carbon::parse($SD))
                            &&
                            Carbon::parse($schedule->startDate)->lessThanOrEqualTo(Carbon::parse($ED))
                            )
                        {
                            $count++;
                        }
                    }
                    $filterText="This Week";
                }
                elseif($filterType == "Yearly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $date=array();
                    $oneDayBeforeWeek = Carbon::parse($dateLocalS)->startOfYear()->subDay()->format('Y-m-d');
                    $oneDayAfterWeek = Carbon::parse($dateLocalS)->endOfYear()->addDay()->format('Y-m-d');


                    

                    $SD=Carbon::parse($dateLocalS)->startOfYear()->format('Y-m-d');
                    $ED=Carbon::parse($SD)->addDay(364)->format('Y-m-d');

                    $schedules = ScheduleSessions2::whereDate('startDate','>=',$oneDayBeforeWeek)
                                                    ->whereDate('startDate','<=',$oneDayAfterWeek)
                                                    ->where('schoolProfileId','=',$school->id)
                                                    ->get();

                    foreach($schedules as $schedule)
                    {
                        $schedule['startDate'] = date('Y-m-d',strtotime($schedule->startDate.$schoolTimeZone." Hours"));
                    }


                    foreach ($schedules as $schedule) {
                        
                        if(Carbon::parse($schedule->startDate)->greaterThanOrEqualTo(Carbon::parse($SD))
                            &&
                            Carbon::parse($schedule->startDate)->lessThanOrEqualTo(Carbon::parse($ED))
                            )
                        {
                            $count++;
                        }
                    }
                    $filterText="This Year";

                }
                elseif($filterType == "Monthly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $date=array();
                    $oneDayBeforeWeek = Carbon::parse($dateLocalS)->startOfMonth()->subDay()->format('Y-m-d');
                    $oneDayAfterWeek = Carbon::parse($dateLocalS)->endOfMonth()->addDay()->format('Y-m-d');


                    $SD=Carbon::parse($dateLocalS)->startOfMonth()->format('Y-m-d');
                    $numberOfDays = Carbon::parse($SD)->daysInMonth;
                    $ED=Carbon::parse($SD)->addDay($numberOfDays-1)->format('Y-m-d');

                    $schedules = ScheduleSessions2::whereDate('startDate','>=',$oneDayBeforeWeek)
                                                    ->whereDate('startDate','<=',$oneDayAfterWeek)
                                                    ->where('schoolProfileId','=',$school->id)
                                                    ->get();

                    foreach($schedules as $schedule)
                    {
                        $schedule['startDate'] = date('Y-m-d',strtotime($schedule->startDate.$schoolTimeZone." Hours"));
                    }

                    foreach ($schedules as $schedule) {
                        
                        if(Carbon::parse($schedule->startDate)->greaterThanOrEqualTo(Carbon::parse($SD))
                            &&
                            Carbon::parse($schedule->startDate)->lessThanOrEqualTo(Carbon::parse($ED))
                            )
                        {
                            $count++;
                        }
                    }
                    $filterText="This Month";
                }
                elseif($filterType == "Today")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));
                    
                    $date=array();
                    $oneDayBeforeWeek = Carbon::parse($dateLocalS)->subDay()->format('Y-m-d');
                    $oneDayAfterWeek = Carbon::parse($dateLocalS)->addDay()->format('Y-m-d');


                    $SD=Carbon::parse($dateLocalS)->format('Y-m-d');

                    $schedules = ScheduleSessions2::whereDate('startDate','>=',$oneDayBeforeWeek)
                                                    ->whereDate('startDate','<=',$oneDayAfterWeek)
                                                    ->where('schoolProfileId','=',$school->id)
                                                    ->get();

                    foreach($schedules as $schedule)
                    {
                        $schedule['startDate'] = date('Y-m-d',strtotime($schedule->startDate.$schoolTimeZone." Hours"));
                    }

                    foreach ($schedules as $schedule) {
            
                        if(Carbon::parse($schedule->startDate)->equalTo(Carbon::parse($SD)))
                        {
                            $count++;
                        }
                    }
                    $filterText="Today";

                }
                
                if ($schedules) 
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
