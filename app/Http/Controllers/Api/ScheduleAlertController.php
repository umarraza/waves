<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/// JWT ///
use JWTAuthException;
use JWTAuth;

/// Models ///
use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiScheduleAlert as ScheduleAlert;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;
use App\Models\Api\ApiStudent as Student;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiSchoolSecondaryAdminProfile as SchoolSecondaryAdminProfile;
use App\Models\Api\ApiScheduleSessions2 as ScheduleSessions2;
use App\Models\Api\ApiChat as Chat;
/// Carbon ///
use Carbon\Carbon;

class ScheduleAlertController extends Controller
{
    // This function will get future alerts list.
    public function getFutureAlerts(Request $request)
    {
        $request['sendDate'] = base64_decode($request->sendDate);
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
        // Checking user is empty or not
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
               'sendDate' => 'required',
               'schoolProfileId' => 'required',
            ];
            // Send Date And School Id is required.
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $request->sendDate  = date('Y-m-d H:i:s',strtotime($request->get('sendDate')));
                // Finding School for school time zone.
                $schoolProfile = SchoolProfiles::find($request->schoolProfileId);
                $schoolTimeZone = $schoolProfile->schoolTimeZone;

                // Checking weather user is Admin or Secondary Admin.
                if($user->isAdmin() || $user->isSecondaryAdmin())
                {
                    $distinct_alerts="";
                    // Getting Admin And Secondary Admin UserId
                    $admin = $schoolProfile->schoolAdminProfile->user->id;

                    $checkSecondaryAdmin = SchoolSecondaryAdminProfile::where('schoolProfileId','=', $request->schoolProfileId)->first();
                    if(!empty($checkSecondaryAdmin))
                    {
                        $secondaryAdmin = $checkSecondaryAdmin->userId;
                        // Finding Alerts.
                        $distinct_alerts = ScheduleAlert::where('schoolProfileId','=',$request->schoolProfileId)->where(function($query) use ($admin, $secondaryAdmin){
                                            $query->where('fromUser', $admin)
                                                  ->orWhere('fromUser', $secondaryAdmin);
                                        })
                                        ->where('sendDate','>=',$request->sendDate)->get();    
                    }
                    else
                    {
                        $distinct_alerts = ScheduleAlert::
                                        where('schoolProfileId','=',$request->schoolProfileId)
                                        ->where('fromUser','=', $admin)
                                        ->where('sendDate','>=',$request->sendDate)
                                        ->get();
                    }
                    
                    //return $schoolTimeZone;
                    // Looping through Result To Change date according to time zone of school.
                    foreach ($distinct_alerts as $res) 
                    {
                        $res->sendDate = date('Y-m-d H:i:s',strtotime($res->sendDate." ".$schoolTimeZone." Hours"));
                    }

                    // If data is success then send success response.
                    if ($distinct_alerts) {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        $response['data']['result'] = base64_encode(json_encode($distinct_alerts));
                        $response['status'] = true;
                    }
                }
                else
                {
                    // In canse of responder find alerts for the specific responder. 
                    $distinct_alerts = ScheduleAlert::where('schoolProfileId','=',$request->schoolProfileId)->where('fromUser', $user->id)
                                        ->where('sendDate','>=',$request->sendDate)
                                        ->get();
                
                    $response['data']['result'] = [];

                    // Loop through result to get all users data to whome alerts are send.
                    foreach ($distinct_alerts as $res) 
                    {
                        $tempArray =[];
                        // Seperating User list.
                        $toUsers = explode(',', $res->toUser);

                        // Looping through user also check if user is still available or not.
                        foreach($toUsers as $toUser)
                        {
                            $user = User::find($toUser);
                            if(!empty($user))
                            {
                                $tempArray[] = $user->Student->getResponseData();    
                            }
                        }
                        $response['data']['result'][] = [ 
                                                            "id" => $res->id,
                                                            "fromUser" => $res->fromUser,
                                                            "sendDate" => date('Y-m-d H:i:s',strtotime($res->sendDate.$schoolTimeZone." Hours")),
                                                            "message" => substr($res->message,14),
                                                            "toUser" => $tempArray
                                                        ];
                    }
                    $response['data']['result'] = base64_encode(json_encode($response['data']['result']));

                    // Checking and sending response.
                    if ($distinct_alerts) {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        $response['status'] = true;
                    }
                }

                
            }
        }
        return $response;
    }

    // Edit Alerts.
    public function editAlert(Request $request)
    { 
        $request['id'] = (int)base64_decode($request->id);
        $request['sendDate'] = base64_decode($request->sendDate);
        $request['message'] = base64_decode($request->message);
        $request['usersData'] = base64_decode($request->usersData);

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
               'id' => ['required','exists:schedules_alert,id'],
               'sendDate' =>'required',
               'message' =>'required',
               'usersData' => 'required'
            ];
            // Rules for id, sending Date of Alert, Message and list of Users.
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                $request->sendDate  = date('Y-m-d H:i:s',strtotime($request->get('sendDate')));
                // If Alert is Edited by Admin or Secondary Admin.
                if($user->isAdmin() || $user->isSecondaryAdmin())
                {

                    // Update Alert
                    $alert = ScheduleAlert::where('id', $request->get('id'))
                        ->update([
                            'message'   => $request->get('message'),
                            'sendDate'  => $request->get('sendDate'),
                            'toUser'   => $request->get('usersData'),
                        ]);
                    
                    if ($alert) 
                    {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        //$response['data']['result'] = $alert;
                        $response['status'] = true;
                    } 

                }
                else
                {
                    // If Alert is edited by responder
                    $usersData = json_decode($request->usersData);
                    $count = 0;
                    $myUserData="";

                    // Concatinating userId's To save in database.
                    foreach ($usersData as $value) 
                    {
                        if($count==0)
                        {
                            $myUserData = $value->userId;
                            $count++;
                        }
                        else
                        {
                            $myUserData = $myUserData.",".$value->userId;
                        }
                    }

                    // Saving result in database.
                    $alert = ScheduleAlert::where('id', $request->get('id'))
                        ->update([
                            'message'   => $request->get('message'),
                            'sendDate'  => $request->get('sendDate'),
                            'toUser'   => $myUserData,
                        ]);

                    if ($alert) 
                    {
                        $response['data']['message'] = 'Request Successfull';
                        $response['data']['code'] = 200;
                        $response['data']['result'] = base64_encode(json_encode($alert));
                        $response['status'] = true;
                    }

                }            
            }
        }
        return $response;
    }

    // Deleting Alert.
    public function deleteAlert(Request $request)
    {
        $request['id'] = (int)base64_decode($request->id);
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
        // Checking if user is valid or not.
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
               'id' => 'required',
            ];
            // Id for alert to be deleted.
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }else
            {
                // Find Alert from id.
                $alert = ScheduleAlert::find($request->id);
                
                // If Alert is deleted successfully then send success response.
                if ($alert->delete()) 
                {
                    $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }
    public  function sentNotificationsA()
    {
        $string = "@asas@asas@";
        $breakString = explode('@', $string);
        return $breakString;
        $currentDate = date('Y-m-d H:i:s');
        $modelSchedules = ScheduleSessions2::where('endDate','>=',$currentDate)->get();
        
        foreach ($modelSchedules as $key => $value) 
        {

            $flag = 0;
            $flag2 = 0;
            $flag3 = 0;

            $schoolTimeSymbol = $value->student->schoolProfile->schoolTimeSymbol;
            $schoolTimeValue  = $value->student->schoolProfile->schoolTimeValue;
            $schoolTimeZone   = $value->student->schoolProfile->schoolTimeZone." Hours";
            
            
            $plus1 =    $schoolTimeValue + 1;
            $plus12 =   $schoolTimeValue + 24; //Variable is used as plus 12
            $plus0 =   $schoolTimeValue + 0; //Variable is used as plus 12

            $plus12 = $schoolTimeSymbol.$plus12;
            $plus1 = $schoolTimeSymbol.$plus1;
            $plus0 = $schoolTimeSymbol.$plus0;

            //$value->description =$plus12.$plus1;
            //$value->save();

            $currentDateTime = strtotime(date('Y-m-d H:i:s',strtotime($plus12.' Hours')));
            $currentDateTimeOneHour = strtotime(date('Y-m-d H:i:s',strtotime($plus1.' Hours')));
            $currentDateTimeNow = strtotime(date('Y-m-d H:i:s',strtotime($plus0.' Hours')));
            


            $startDateTimeStamp = strtotime($value->startDate.$schoolTimeZone);
            

            //return date('Y-m-d H:i',$currentDateTime)." ".date('Y-m-d H:i',$startDateTimeStamp); 
            
            //return date('H:i',$currentDateTimeOneHour)." ".date('H:i',$startDateTimeStamp); 
            //return date('H:i',$startDateTimeStamp);
            if(date('Y-m-d H:i',$currentDateTime) == date('Y-m-d H:i',$startDateTimeStamp)){
                 $flag = 1;
                 //return "flag 1 mai agea hai:";
            }
            if(date('Y-m-d H:i',$currentDateTimeOneHour) == date('Y-m-d H:i',$startDateTimeStamp)){
                $flag2 = 1;   
                //return "flag 2 mai agea hai:";
            }
            if(date('Y-m-d H:i',$currentDateTimeNow) == date('Y-m-d H:i',$startDateTimeStamp)){
                $flag3 = 1;   
                //return "flag 2 mai agea hai:";
            }
            if($flag)
            {
                // Send to Student
                $message = 'Your upcoming session in next 24 hours with '.$value->responder->user->getName();
                $value->student->user->sendPushNotification($message,"schedule");
                //Send to Responder
                $message = 'Your upcoming session in next 24 hours with '.$value->student->user->getName();
                $value->responder->user->sendPushNotification($message,"schedule");
            }
            if($flag2)
            {   
                //Send to Student
                $message = 'Your upcoming session in next 1 hours with '.$value->responder->user->getName();
                $value->student->user->sendPushNotification($message,"schedule");
                //$value->description =$message;
                //$value->save();
                //Send to Responder
                $message = 'Your upcoming session in next 1 hours with '.$value->student->user->getName();
                $value->responder->user->sendPushNotification($message,"schedule");
            }
            if($flag3)
            {   
                //Send to Student
                $message = 'You have a session now with '.$value->responder->user->getName();
                $value->student->user->sendPushNotification($message,"schedule");
                //$value->description =$message;
                //$value->save();
                //Send to Responder
                $message = 'You have a session now with '.$value->student->user->getName();
                $value->responder->user->sendPushNotification($message,"schedule");
            }
        }
    }
    public  function sentNotificationsAB()
    {
        $school = SchoolProfiles::find(36);
        $schoolTimeZone = $school->schoolTimeZone;

        $filterType = "Monthly";


        if($filterType=="Weekly")
        {

            $dateLocalS = date('Y-m-d',strtotime($schoolTimeZone." Hours"));

            $date=array();
            $oneDayBeforeWeek = Carbon::parse($dateLocalS)->startOfWeek()->subDay()->format('Y-m-d');
            $oneDayAfterWeek = Carbon::parse($dateLocalS)->endOfWeek()->addDay()->format('Y-m-d');

            $SD=Carbon::parse($dateLocalS)->startOfWeek()->format('Y-m-d');
            $ED=Carbon::parse($SD)->addDay(6)->format('Y-m-d');

            $date[0]=$SD;

            for($i=1; $i<=6 ; $i++ )
            {
                $date[$i]=Carbon::parse($date[0])->addDay($i)->format('Y-m-d');
            }

            $chats = Chat::whereDate('createdAt','>=',$oneDayBeforeWeek)
                              ->whereDate('createdAt','<=',$oneDayAfterWeek)
                              ->where('schoolProfileId','=',$school->id)
                              ->get();



            foreach($chats as $chat)
            {
                $chat['sendDate'] = date('Y-m-d',strtotime($chat->createdAt.$schoolTimeZone." Hours"));
            }

            $count=array();
            $count[0]=0;
            for($i=1; $i<=6 ; $i++ )
            {
                $count[$i]=0;
            }


            $days=array();
            $days[0]='Monday';
            $days[1]='Tuesday';
            $days[2]='Wednesday';
            $days[3]='Thursday';
            $days[4]='Friday';
            $days[5]='Saturday';
            $days[6]='Sunday';
            

            foreach ($chats as $chat) {
                if($date[0]==$chat->sendDate)
                {
                    $count[0]++;
                }
                elseif($date[1]==$chat->sendDate)
                {
                    $count[1]++;
                }
                elseif($date[2]==$chat->sendDate)
                {
                    $count[2]++;
                }
                elseif($date[3]==$chat->sendDate)
                {
                    $count[3]++;
                }
                elseif($date[4]==$chat->sendDate)
                {
                    $count[4]++;
                }
                elseif($date[5]==$chat->sendDate)
                {
                    $count[5]++;
                }
                elseif($date[6]==$chat->sendDate)
                {
                    $count[6]++;
                }
            }

            if ($chats) 
            {
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['status'] = true;
                $response['data']['result']['counts'] = $count;
                $response['data']['result']['label'] = $days;
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

            $date[0]=Carbon::parse($SD)->startOfYear()->format('Y-m');

            for($i=1; $i<=11 ; $i++ )
            {
                $date[$i]=Carbon::parse($date[0])->addMonth($i)->format('Y-m');
            }

            $chats = Chat::whereDate('createdAt','>=',$oneDayBeforeWeek)
                              ->whereDate('createdAt','<=',$oneDayAfterWeek)
                              ->where('schoolProfileId','=',$school->id)
                              ->get();



            foreach($chats as $chat)
            {
                $chat['sendDate'] = date('Y-m',strtotime($chat->createdAt.$schoolTimeZone." Hours"));
            }

            $count=array();
            $count[0]=0;
            for($i=1; $i<=11 ; $i++ )
            {
                $count[$i]=0;
            }


            $months=array();
            $months[0]    ='Janurary';
            $months[1]    ='Febuary';
            $months[2]    ='March';
            $months[3]    ='April';
            $months[4]    ='May';
            $months[5]    ='June';
            $months[6]    ='July';
            $months[7]    ='August';
            $months[8]    ='September';
            $months[9]    ='October';
            $months[10]   ='November';
            $months[11]   ='December';

            

            foreach ($chats as $chat) {
                if($date[0]==$chat->sendDate)
                {
                    $count[0]++;
                }
                elseif($date[1]==$chat->sendDate)
                {
                    $count[1]++;
                }
                elseif($date[2]==$chat->sendDate)
                {
                    $count[2]++;
                }
                elseif($date[3]==$chat->sendDate)
                {
                    $count[3]++;
                }
                elseif($date[4]==$chat->sendDate)
                {
                    $count[4]++;
                }
                elseif($date[5]==$chat->sendDate)
                {
                    $count[5]++;
                }
                elseif($date[6]==$chat->sendDate)
                {
                    $count[6]++;
                }
                elseif($date[7]==$chat->sendDate)
                {
                    $count[7]++;
                }
                elseif($date[8]==$chat->sendDate)
                {
                    $count[8]++;
                }
                elseif($date[9]==$chat->sendDate)
                {
                    $count[9]++;
                }
                elseif($date[10]==$chat->sendDate)
                {
                    $count[10]++;
                }
                elseif($date[11]==$chat->sendDate)
                {
                    $count[11]++;
                }
            }

            if ($chats) 
            {
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['status'] = true;
                $response['data']['result']['counts'] = $count;
                $response['data']['result']['label'] = $months;
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

            $date[0]=Carbon::parse($SD)->startOfMonth()->format('d');

            for($i=1; $i<=$numberOfDays-1 ; $i++ )
            {
                $date[$i]=Carbon::parse($date[0])->addDay($i)->format('d');
            }

            $chats = Chat::whereDate('createdAt','>=',$oneDayBeforeWeek)
                              ->whereDate('createdAt','<=',$oneDayAfterWeek)
                              ->where('schoolProfileId','=',$school->id)
                              ->get();

            foreach($chats as $chat)
            {
                $chat['sendDate'] = date('d',strtotime($chat->createdAt.$schoolTimeZone." Hours"));
            }

            $count=array();
            $count[0]=0;
            for($i=1; $i<=$numberOfDays-1 ; $i++ )
            {
                $count[$i]=0;
            }

            $daysDates=array();
            $daysDates[0] =1;
            for($i=1; $i<=$numberOfDays-1 ; $i++ )
            {
                $daysDates[$i]=$i+1;
            }
            
            foreach ($chats as $chat) 
            {
                for($i=0; $i<=$numberOfDays-1 ; $i++ )
                {
                    if($date[$i]==$chat->sendDate)
                    {
                        $count[$i]++;
                    }
                }
            }

            if ($chats) 
            {
                $response['data']['message'] = 'Request Successfull';
                $response['data']['code'] = 200;
                $response['status'] = true;
                $response['data']['result']['counts'] = $count;
                $response['data']['result']['label'] = $daysDates;
            }
        }

        
        return "jhihihi";
        echo crypt('something','st');//hash_hmac('ripemd160', 'The quick brown fox jumped over the lazy dog.', 'secret');

        return "hahaha";

        $schedules_alert = ScheduleAlert::find(160);
        $key = 'bRuD5WYw5wd0rdHR9yLlM6wt2vteuiniQBqE70nAuhU=';

        echo base64_decode($key);

        $password_plain = 'abc123';
        echo "<br>".$password_plain . "<br>";

        //our data being encrypted. This encrypted data will probably be going into a database
        //since it's base64 encoded, it can go straight into a varchar or text database field without corruption worry
        $password_encrypted = $schedules_alert->my_encrypt($password_plain, $key);
        echo $password_encrypted."<br>";

        //now we turn our encrypted data back to plain text
        $password_decrypted = $schedules_alert->my_decrypt($password_encrypted, $key);
        echo $password_decrypted . "<br>";


        return "hahahaha";

        // $key = base64_encode(openssl_random_pseudo_bytes(32));
        // $data = "abc123";
        // // Remove the base64 encoding from our key
        // $encryption_key = base64_decode($key);
        // // Generate an initialization vector
        // $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        // // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
        // $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        // // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
        // return base64_encode($encrypted . '::' . $iv);



        $var =  encrypt("haha");
        $var2 = base64_decode($var);

        $var2=base64_encode("YqPmqallsLxQeqfmoKaDuw==");
        //echo $var."<br><br>";


        $var1 = decrypt($var);


        //echo $var1."<br><br>";
        $response['data']['message'] = 'Request Successfull';
                    $response['data']['code'] = 200;
                    $response['status'] = true;
                    $response['result'] = $var;
                    $response['orignal'] = $var1;
                    $response['other'] = $var2;

        return $response;

        $oldAlerts = ScheduleAlert::where('sendDate','=',date('Y-m-d H:i'))->get();
        foreach ($oldAlerts as $alert) 
        {
            $user = User::find($alert->fromUser);
            if(!empty($user))
            {
                $schoolTimeZone="";
                $schoolProfileId=$alert->schoolProfileId;

                if($user->isAdmin())
                {
                    $schoolTimeZone   = $user->schoolAdminProfile->schoolProfile->schoolTimeZone." Hours";
                }
                elseif($user->isSecondaryAdmin())
                {
                    $schoolTimeZone   = $user->schoolSecondaryAdminProfile->schoolProfile->schoolTimeZone." Hours";
                }
                else
                {
                    $schoolTimeZone   = $user->responder->schoolProfile->schoolTimeZone." Hours";
                }


                $currentDateTime = strtotime(date('Y-m-d H:i:s',strtotime($schoolTimeZone)));
                $alertSendDate = strtotime($alert->sendDate.$schoolTimeZone);

                
                if(date('H:i',$currentDateTime) == date('H:i',$alertSendDate))
                {

                    if($alert->toUser == "Students & Responders")
                    {
                        $student = Student::where('schoolProfileId','=',$schoolProfileId)
                                                        ->get();
                        foreach($student as $std)
                        {
                            if($std->user->verified == 1)
                            {
                                $message = $user->getName().': '.$alert->message;
                                $std->user->sendPushNotification($message,"message");
                            }
                        }

                        $responder = Responder::where('schoolProfileId','=',$schoolProfileId)
                                                ->get();

                        foreach($responder as $res)
                        {
                            if($res->user->verified == 1)
                            {
                                $message = $user->getName().': '.$alert->message;
                                $res->user->sendPushNotification($message,"message");
                            }
                        }
                    }
                    elseif($alert->toUser == "Responders")
                    {
                        $responder = Responder::where('schoolProfileId','=',$schoolProfileId)
                                                        ->get();

                        foreach($responder as $res)
                        {
                            if($res->user->verified == 1)
                            {
                                $message = $user->getName().': '.$alert->message;
                                $res->user->sendPushNotification($message,"message");
                            }
                        }
                    }
                    elseif($alert->toUser == "Students")
                    {
                        $student = Student::where('schoolProfileId','=',$schoolProfileId)
                                                        ->get();
                        foreach($student as $std)
                        {
                            if($std->user->verified == 1)
                            {
                                $message = $user->getName().': '.$alert->message;
                                $std->user->sendPushNotification($message,"message");
                            }
                        }
                    }
                    else
                    {
                        $fromUser = $user->id;
                        $toUsers = explode(',', $alert->toUser);
                        foreach ($toUsers as $toUser) 
                        {
                            $isValidUser = User::where(['id' => $toUser])->first();
                            if(!empty($isValidUser))
                            {
                                $modelThread = Threads::where(function($query) use ($fromUser,$toUser) {
                                      return $query->where('fromUser','=',$fromUser)
                                              ->where('toUser','=',$toUser)
                                              ->where('type','=',Threads::TYPE_NON_ANONYMOUS);
                                      })
                                   ->orWhere(function($query) use ($fromUser,$toUser) {
                                          return $query->where('fromUser','=',$toUser)
                                              ->where('toUser','=',$fromUser)
                                              ->where('type','=',Threads::TYPE_NON_ANONYMOUS);
                                      })
                                   ->first();

                                if(empty($modelThread))
                                {
                                    $modelThread = Threads::create([
                                        'fromUser'      => $fromUser,
                                        'toUser'        => $toUser,
                                        'threadName'    => Threads::uniqueThreadName($fromUser),
                                        'threadLabel'   => Threads::uniqueThreadLabel($fromUser,$toUser,0),
                                        'type'          => Threads::TYPE_NON_ANONYMOUS,
                                        'anonimityFlag' => Threads::TYPE_NON_ANONYMOUS,
                                        'schoolProfileId'=>$schoolProfileId,
                                    ]);
                                    
                                }
                                $modelChat = Chat::create([
                                    'message'   => $alert->message,
                                    'fromUser'  => $fromUser,
                                    'toUser'    => $toUser,
                                    'threadId'=> $modelThread->id,
                                    'schoolProfileId'=>$schoolProfileId,
                                ]);

                                if(!empty($modelChat)){
                                    $message = $user->getName().': '.$modelChat->message;
                                    $isValidUser->sendPushNotification($message,"message");
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
