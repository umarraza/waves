<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class ScheduleSessions extends Model
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
    protected $table = 'schedule_sessions';

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
        'causeData'
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
                'responderProfile'  => $this->responder->getResponseData(),
                'type'              => $this->type,
                'repeated'          => $this->repeated,
                'description'       => $this->description,
                'status'            => $this->status,
                'startDate'         => date('Y-m-d H:i',strtotime($this->startDate)),
                'endDate'           => date('Y-m-d H:i',strtotime($this->endDate)),
                'createdAt'         => date('Y-m-d H:i:s',strtotime($this->createdAt)),
                'causeData'         => $this->causeData,
                'validity'          => $this->validity()
            ];

        if($this->repeated == self::NOT_REPEATED && !empty($this->rEndDate))
            $response['rEndDate']   =  date('Y-m-d H:i:s',strtotime($this->rEndDate));

        return $response;
    }

    public function validity(){
        if($this->type == self::TYPE_NEVER)
           return (strtotime($this->endDate) >= strtotime('now')) ? true : false;
        else{
            if($this->repeated == self::REPEATED)
                return true;
            else
                return (strtotime($this->rEndDate) >= strtotime('now')) ? true : false;
        }
    }

    public static function sentNotifications(){
        $modelSchedules = ScheduleSessions::where('type','=',self::TYPE_NEVER)
            ->orWhere(function($query) {
                return $query->where('repeated','=',self::REPEATED)
                    ->orWhere('rEndDate','>=',date('Y-m-d H:i:s'));
            })
            ->get();
        $currentDateTime = strtotime(date('Y-m-d H:i:s',strtotime('+12 Hours')));
        foreach ($modelSchedules as $key => $value) {
            $flag = 0;
            $startDateTimeStamp = strtotime($value->startDate);
            if($value->type == self::TYPE_NEVER && date('H:i',$currentDateTime) == date('H:i',$startDateTimeStamp)){
                $flag = 1;
            }
            else{
                if($value->repeated == self::REPEATED){
                    if ($value->type == self::TYPE_DAILY && date('H:i',$currentDateTime) == date('H:i',$startDateTimeStamp)) {
                        $flag = 1;
                    }
                    elseif ($value->type == self::TYPE_WEEKLY && date('H:i',$currentDateTime) == date('H:i',$startDateTimeStamp) && date('w',$currentDateTime) == date('w',$startDateTimeStamp)) {
                        $flag = 1;
                    }
                    elseif ($value->type == self::TYPE_MONTHLY && date('H:i',$currentDateTime) == date('H:i',$startDateTimeStamp) && date('d',$currentDateTime) == date('d',$startDateTimeStamp)) {
                        $flag = 1;
                    }
                }
                elseif(strtotime($value->rEndDate) >= strtotime('now')){
                    if ($value->type == self::TYPE_DAILY && date('H:i',$currentDateTime) == date('H:i',$startDateTimeStamp)) {
                        $flag = 1;
                    }
                    elseif ($value->type == self::TYPE_WEEKLY && date('H:i',$currentDateTime) == date('H:i',$startDateTimeStamp) && date('w',$currentDateTime) == date('w',$startDateTimeStamp)) {
                        $flag = 1;
                    }
                    elseif ($value->type == self::TYPE_MONTHLY && date('H:i',$currentDateTime) == date('H:i',$startDateTimeStamp) && date('d',$currentDateTime) == date('d',$startDateTimeStamp)) {
                        $flag = 1;
                    }
                }
            }
            if($flag){
                // Send to Student
                $message = 'Your upcoming session in next 12 hours with '.$value->responder->user->getName().' ('.$modelSchedules->description.').';
                $value->student->user->sendPushNotification($message,"schedule");
                //Send to Responder
                $message = 'Your upcoming session in next 12 hours with '.$value->student->user->getName().' ('.$modelSchedules->description.').';
                $value->responder->user->sendPushNotification($message,"schedule");
            }
        }
    }
}
