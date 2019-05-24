<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class ScheduleSessions2 extends Model
{
    //Attributes Constants
    const CREATED_AT    = 'createdAt';
    const UPDATED_AT    = 'updatedAt';
    const TYPE_NEVER    = 0;
    const TYPE_DAILY    = 1;
    const TYPE_WEEKLY   = 2;
    const TYPE_MONTHLY  = 3;
    const NOT_REPEATED  = 0;
    const REPEATED  = 1;
    const ACTIVE  = 1;
    const IN_ACTIVE  = 0;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'schedule_sessions2';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'studentProfileId',
        'responderProfileId',
        'type',
        'repeated',
        'description',
        'status',
        'startDate',
        'endDate',
        'rEndDate',
        'sessionId',
        'causeData',
        'schoolProfileId',
        'isDeleted',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * @return mixed
     */
    public function student()
    {
        return $this->hasOne(Student::class,'id','studentProfileId');
    }

    /**
     * @return mixed
     */
    public function responder()
    {
        return $this->hasOne(Responder::class,'id','responderProfileId');
    }

    /**
     * @return Array
     */
    public static function getTypes(){
        return [
                self::TYPE_NEVER    => 'Never', 
                self::TYPE_DAILY    => 'Daily',
                self::TYPE_WEEKLY   => 'Weekly',
                self::TYPE_MONTHLY  => 'Monthly'
            ];
    }

    public function getArrayResponse(){
        $response = [
                'id'                => $this->id,
                'studentProfile'    => $this->student->getResponseData(),
                'studentFullName'   => $this->student->fullName(),
                'responderProfile'  => $this->responder->getResponseData(),
                'type'              => $this->type,
                'repeated'          => $this->repeated,
                'description'       => $this->description,
                'sessionId'         => $this->sessionId,
                'status'            => $this->status,
                'startDateInnerUse' => $this->startDate,
                'endDateInnerUse'   => $this->endDate,
                'startDate'         => date('Y-m-d H:i',strtotime($this->startDate)),
                'endDate'           => date('Y-m-d H:i',strtotime($this->endDate)),
                'createdAt'         => date('Y-m-d H:i:s',strtotime($this->createdAt)),
                'causeData'         => $this->causeData,
                'validity'          => $this->validity(),
                'isDeleted'         => $this->isDeleted,
            ];

        if($this->repeated == self::NOT_REPEATED && !empty($this->rEndDate))
        {
            $response['rEndDate']   =  date('Y-m-d H:i:s',strtotime($this->rEndDate));
            $response['rEndDateInnerUse']   =  date('Y-m-d H:i:s',strtotime($this->rEndDate));
        }
        return $response;
    }

    public function validity()
    {
        return (strtotime($this->endDate) >= strtotime('now')) ? true : false;
    }

    public static function sentNotifications(){

        $currentDate = date('Y-m-d H:i:s');
        $modelSchedules = ScheduleSessions2::where('endDate','>=',$currentDate)->where('isDeleted','=',0)->get();
        
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
}
