<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Api\ApiWaves as Waves;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiChat as Chat;
use App\Models\Api\ApiThreads as Threads;
use App\Models\Api\ApiScheduleSessions2 as ScheduleSessions2;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;
use Carbon\Carbon;
use JWTAuthException;
use JWTAuth;

class WavesController extends Controller
{
    public function listWaves(Request $request)
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
        if(!empty($user) && $user->statusVerified() && !$user->isSuperAdmin())
        {
        	$response = [
                'data' => [
                    'code' => 400,
                   	'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];
            $rules = [
               'schoolProfileId' => ['required','exists:school_profiles,id'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
            	$waves = Waves::all();

                if ($waves) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result'] = base64_encode(json_encode($waves));
                    $response['status'] = true;
                }
            }
        }
        else
        {
            if($user->isSuperAdmin())
            {
                $waves = Waves::all();

                if ($waves) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['data']['result'] = base64_encode(json_encode($waves));
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }


    public function addWaves(Request $request)
    {
        $request['name'] = base64_decode($request->name);
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'error'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified() && $user->isSuperAdmin())
        {
            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];
            $rules = [
               'name' => ['required'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $waves = Waves::create(["name" => $request->name]);

                if ($waves) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    public function editWaves(Request $request)
    {
        $request['name'] = base64_decode($request->name);
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
        if(!empty($user) && $user->statusVerified() && $user->isSuperAdmin())
        {
            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];
            $rules = [
                'id' => ['required','exists:waves,id'],
                'name' => ['required'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $waves = Waves::where('id','=',$request->id)->update(["name" => $request->name]);

                if ($waves) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    public function delWaves(Request $request)
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
        if(!empty($user) && $user->statusVerified() && $user->isSuperAdmin())
        {
            $response = [
                'data' => [
                    'code' => 400,
                    'message' => 'Something went wrong. Please try again later!',
                ],
               'status' => false
            ];
            $rules = [
                'id' => ['required','exists:waves,id']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $waves = Waves::find($request->id);

                if ($waves->delete()) {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
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
                $waves = Waves::all();
                $waveslist=[];
                foreach ($waves as $wave) {
                    $waveslist[]=$wave->name;
                }

                $counters=[];
                foreach ($waveslist as $wave) {
                    $counters[$wave]=0;
                }

                $threads = Threads::where('schoolProfileId','=',$request->schoolProfileId)->get();
                $sessions = ScheduleSessions2::where('schoolProfileId','=',$request->schoolProfileId)->get();

                foreach ($threads as $thread ) 
                {

                    if($thread->causeData)
                    {
                        for ($i=0 ; $i<count($waves) ; $i++) 
                        {
                            $removedInvertedCommas = str_replace("\"", "",$thread->causeData);
                            $removedBraces = str_replace("[","",$removedInvertedCommas);
                            $removedBraces = str_replace("]","",$removedBraces);
                            $causeData = explode(',',$removedBraces);
                            if(in_array($waveslist[$i], $causeData))
                            {
                                $counters[$waveslist[$i]]++;
                            }
                            // if(preg_match('('.$waveslist[$i].')', $thread->causeData))
                            // {
                            //     $counters[$waveslist[$i]]++;
                            // }
                        }
                    }
                    
                }
                foreach ($sessions as $session ) 
                {

                    if($session->causeData)
                    {
                        $removedInvertedCommas = str_replace("\"", "",$session->causeData);
                        $removedBraces = str_replace("[","",$removedInvertedCommas);
                        $removedBraces = str_replace("]","",$removedBraces);
                        $causeData = explode(',',$removedBraces);
                        for ($i=0 ; $i<count($waves) ; $i++) 
                        {
                            if(in_array($waveslist[$i], $causeData))
                            {
                                $counters[$waveslist[$i]]++;
                            }

                            // if(preg_match('('.$waveslist[$i].')', $session->causeData))
                            // {
                            //     $counters[$waveslist[$i]]++;
                            // }
                        }
                    }
                    
                }

                
                arsort($counters);
                $topCounterValue = array_slice($counters,0,5);
                $topCounterWaves = array_keys($topCounterValue);
                
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['status'] = true;
                $response['data']['result']['counters'] = $topCounterValue;
                $response['data']['result']['waveslist']= $topCounterWaves;
                $response['data']['result']['countersAll'] = $counters;
                $response['data']['result']['waveslistAll']= $waveslist;
                 
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
               'filter'    => ['required'],
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


                $waves = Waves::all();
                $waveslist=[];
                foreach ($waves as $wave) {
                    $waveslist[]=$wave->name;
                }

                $counters=[];
                foreach ($waveslist as $wave) {
                    $counters[$wave]=0;
                }


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
                           if($schedule->causeData)
                           {
                               $removedInvertedCommas = str_replace("\"", "",$schedule->causeData);
                               $removedBraces = str_replace("[","",$removedInvertedCommas);
                               $removedBraces = str_replace("]","",$removedBraces);
                               $causeData = explode(',',$removedBraces);
                               for ($i=0 ; $i<count($waves) ; $i++) 
                               {
                                   if(in_array($waveslist[$i], $causeData))
                                   {
                                       $counters[$waveslist[$i]]++;
                                   }
                               }
                           } 
                        }
                    }
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
                            if($schedule->causeData)
                            {
                                $removedInvertedCommas = str_replace("\"", "",$schedule->causeData);
                                $removedBraces = str_replace("[","",$removedInvertedCommas);
                                $removedBraces = str_replace("]","",$removedBraces);
                                $causeData = explode(',',$removedBraces);
                                for ($i=0 ; $i<count($waves) ; $i++) 
                                {
                                    if(in_array($waveslist[$i], $causeData))
                                    {
                                        $counters[$waveslist[$i]]++;
                                    }
                                }
                            } 
                        }
                    }
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
                            if($schedule->causeData)
                            {
                                $removedInvertedCommas = str_replace("\"", "",$schedule->causeData);
                                $removedBraces = str_replace("[","",$removedInvertedCommas);
                                $removedBraces = str_replace("]","",$removedBraces);
                                $causeData = explode(',',$removedBraces);
                                for ($i=0 ; $i<count($waves) ; $i++) 
                                {
                                    if(in_array($waveslist[$i], $causeData))
                                    {
                                        $counters[$waveslist[$i]]++;
                                    }
                                }
                            }
                        }
                    }
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
                            if($schedule->causeData)
                            {
                                $removedInvertedCommas = str_replace("\"", "",$schedule->causeData);
                                $removedBraces = str_replace("[","",$removedInvertedCommas);
                                $removedBraces = str_replace("]","",$removedBraces);
                                $causeData = explode(',',$removedBraces);
                                for ($i=0 ; $i<count($waves) ; $i++) 
                                {
                                    if(in_array($waveslist[$i], $causeData))
                                    {
                                        $counters[$waveslist[$i]]++;
                                    }
                                }
                            }
                        }
                    }
                }



                if($filterType=="Weekly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $date=array();
                    $oneDayBeforeWeek = Carbon::parse($dateLocalS)->startOfWeek()->subDay()->format('Y-m-d');
                    $oneDayAfterWeek = Carbon::parse($dateLocalS)->endOfWeek()->addDay()->format('Y-m-d');

                    $SD=Carbon::parse($dateLocalS)->startOfWeek()->format('Y-m-d');
                    $ED=Carbon::parse($SD)->addDay(6)->format('Y-m-d');

                    $threads = Threads::whereDate('createdAt','>=',$oneDayBeforeWeek)
                                      ->whereDate('createdAt','<=',$oneDayAfterWeek)
                                      ->where('schoolProfileId','=',$school->id)
                                      ->get();

                    foreach($threads as $thread)
                    {
                        $thread['createdAt'] = date('Y-m-d',strtotime($thread->createdAt.$schoolTimeZone." Hours"));
                    }

                    foreach ($threads as $thread) 
                    {
                        
                        if(Carbon::parse($thread->createdAt)->greaterThanOrEqualTo(Carbon::parse($SD))
                            &&
                            Carbon::parse($thread->createdAt)->lessThanOrEqualTo(Carbon::parse($ED))
                            )
                        {
                            if($thread->causeData)
                            {
                                for ($i=0 ; $i<count($waves) ; $i++) 
                                {
                                    $removedInvertedCommas = str_replace("\"", "",$thread->causeData);
                                    $removedBraces = str_replace("[","",$removedInvertedCommas);
                                    $removedBraces = str_replace("]","",$removedBraces);
                                    $causeData = explode(',',$removedBraces);
                                    if(in_array($waveslist[$i], $causeData))
                                    {
                                        $counters[$waveslist[$i]]++;
                                    }
                                }
                            } 
                        }
                    }
                }
                elseif($filterType == "Yearly")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

                    $date=array();
                    $oneDayBeforeWeek = Carbon::parse($dateLocalS)->startOfYear()->subDay()->format('Y-m-d');
                    $oneDayAfterWeek = Carbon::parse($dateLocalS)->endOfYear()->addDay()->format('Y-m-d');


                    

                    $SD=Carbon::parse($dateLocalS)->startOfYear()->format('Y-m-d');
                    $ED=Carbon::parse($SD)->addDay(364)->format('Y-m-d');

                    $threads = Threads::whereDate('createdAt','>=',$oneDayBeforeWeek)
                                      ->whereDate('createdAt','<=',$oneDayAfterWeek)
                                      ->where('schoolProfileId','=',$school->id)
                                      ->get();

                    foreach($threads as $thread)
                    {
                        $thread['createdAt'] = date('Y-m-d',strtotime($thread->createdAt.$schoolTimeZone." Hours"));
                    }


                    foreach ($threads as $thread) {
                        
                        if(Carbon::parse($thread->createdAt)->greaterThanOrEqualTo(Carbon::parse($SD))
                            &&
                            Carbon::parse($thread->createdAt)->lessThanOrEqualTo(Carbon::parse($ED))
                            )
                        {
                            if($thread->causeData)
                            {
                                for ($i=0 ; $i<count($waves) ; $i++) 
                                {
                                    $removedInvertedCommas = str_replace("\"", "",$thread->causeData);
                                    $removedBraces = str_replace("[","",$removedInvertedCommas);
                                    $removedBraces = str_replace("]","",$removedBraces);
                                    $causeData = explode(',',$removedBraces);
                                    if(in_array($waveslist[$i], $causeData))
                                    {
                                        $counters[$waveslist[$i]]++;
                                    }
                                }
                            } 
                        }
                    }
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

                    $threads = Threads::whereDate('createdAt','>=',$oneDayBeforeWeek)
                                      ->whereDate('createdAt','<=',$oneDayAfterWeek)
                                      ->where('schoolProfileId','=',$school->id)
                                      ->get();

                    foreach($threads as $thread)
                    {
                        $thread['createdAt'] = date('Y-m-d',strtotime($thread->createdAt.$schoolTimeZone." Hours"));
                    }

                    foreach ($threads as $thread) {
                        
                        if(Carbon::parse($thread->createdAt)->greaterThanOrEqualTo(Carbon::parse($SD))
                            &&
                            Carbon::parse($thread->createdAt)->lessThanOrEqualTo(Carbon::parse($ED))
                            )
                        {
                            if($thread->causeData)
                            {
                                for ($i=0 ; $i<count($waves) ; $i++) 
                                {
                                    $removedInvertedCommas = str_replace("\"", "",$thread->causeData);
                                    $removedBraces = str_replace("[","",$removedInvertedCommas);
                                    $removedBraces = str_replace("]","",$removedBraces);
                                    $causeData = explode(',',$removedBraces);
                                    if(in_array($waveslist[$i], $causeData))
                                    {
                                        $counters[$waveslist[$i]]++;
                                    }
                                }
                            }
                        }
                    }
                }
                elseif($filterType == "Today")
                {
                    $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));
                    
                    $date=array();
                    $oneDayBeforeWeek = Carbon::parse($dateLocalS)->subDay()->format('Y-m-d');
                    $oneDayAfterWeek = Carbon::parse($dateLocalS)->addDay()->format('Y-m-d');


                    $SD=Carbon::parse($dateLocalS)->format('Y-m-d');

                    $threads = Threads::whereDate('createdAt','>=',$oneDayBeforeWeek)
                                      ->whereDate('createdAt','<=',$oneDayAfterWeek)
                                      ->where('schoolProfileId','=',$school->id)
                                      ->get();

                    foreach($threads as $thread)
                    {
                        $thread['createdAt'] = date('Y-m-d',strtotime($thread->createdAt.$schoolTimeZone." Hours"));
                    }

                    foreach ($threads as $thread) {
            
                        if(Carbon::parse($thread->createdAt)->equalTo(Carbon::parse($SD)))
                        {
                            if($thread->causeData)
                            {
                                for ($i=0 ; $i<count($waves) ; $i++) 
                                {
                                    $removedInvertedCommas = str_replace("\"", "",$thread->causeData);
                                    $removedBraces = str_replace("[","",$removedInvertedCommas);
                                    $removedBraces = str_replace("]","",$removedBraces);
                                    $causeData = explode(',',$removedBraces);
                                    if(in_array($waveslist[$i], $causeData))
                                    {
                                        $counters[$waveslist[$i]]++;
                                    }
                                }
                            }
                        }
                    }
                }
                arsort($counters);
                $topCounterValue = array_slice($counters,0,5);
                $topCounterWaves = array_keys($topCounterValue);
                
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['status'] = true;
                $response['data']['result']['counters'] = $topCounterValue;
                $response['data']['result']['waveslist']= $topCounterWaves;
                $response['data']['result']['countersAll'] = $counters;
                $response['data']['result']['waveslistAll']= $waveslist;
                 
            }
            $response['data']['result'] = base64_encode(json_encode($response['data']['result']));
        }
        return $response;
    }
}
