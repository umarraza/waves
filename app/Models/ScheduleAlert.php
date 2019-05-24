<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleAlert extends Model
{
    //Status Constants
	const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'schedules_alert';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fromUser',
        'toUser',
        'message',
        'sendDate',
        'status',
        'schoolProfileId'
    ];

    /**
     * @return mixed
     */
    public function responder()
    {
        return $this->hasOne(Responder::class,'id','fromUser');
    }


    public static function sentNotificationsOld()
    {
    	$oldAlerts = ScheduleAlert::where('sendDate','=',date('Y-m-d H:i'))->get();
        foreach ($oldAlerts as $alert) 
        {

            // $schoolTimeSymbol = $value->student->schoolProfile->schoolTimeSymbol;
            // $schoolTimeValue  = $value->student->schoolProfile->schoolTimeValue;
            $user = User::find($alert->fromUser);
            if($user->isAdmin())
            {
                $schoolTimeZone   = $user->schoolAdminProfile->schoolProfile->schoolTimeZone." Hours";
            }
            else
            {
                $schoolTimeZone   = $user->responder->schoolProfile->schoolTimeZone." Hours";    
            }
            

            $currentDateTime = strtotime(date('Y-m-d H:i:s',strtotime($schoolTimeZone)));
            $alertSendDate = strtotime($alert->sendDate.$schoolTimeZone);

            
            if(date('H:i',$currentDateTime) == date('H:i',$alertSendDate))
            {
                if(!empty($user))
                {
                    $fromUser = $user->id;
                    $toUser = $alert->toUser;
                    $isValidUser = User::where(['id' => $toUser])->first();
                    if(!empty($isValidUser))
                    {
                        if($isValidUser->isStudent())
                        {
                            $schoolProfileId = $isValidUser->Student->schoolProfileId;
                        }
                        else
                        {
                            $schoolProfileId = $isValidUser->Responder->schoolProfileId;
                        }
                        if(!$user->isAdmin())
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
                        else
                        {
                            $message = $user->getName().': '.$alert->message;
                            $isValidUser->sendPushNotification($message,"message");
                        }
                    }
                }
            }
        }
    }

    public static function sentNotifications()
    {
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

function my_encrypt($data, $key) {
    // Remove the base64 encoding from our key
    $encryption_key = base64_decode($key);
    // Generate an initialization vector
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
    return base64_encode($encrypted . '::' . $iv);
}

function my_decrypt($data, $key) {
    // Remove the base64 encoding from our key
    $encryption_key = base64_decode($key);
    // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);

    return $iv;
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}
}
