<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Api\ApiScheduleSessions as ScheduleSessions;
use App\Models\Api\ApiScheduleSessions2 as ScheduleSessions2;
use App\Models\Api\ApiUser as User;
use Illuminate\Support\Facades\DB;

class StudentResponder extends Model
{
    //Status Constants
	const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'responder_students';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'studentProfileId',
        'responderProfileId',
        'verified'
    ];

    /**
     * @return mixed
     */
    public function studentProfile()
    {
        return $this->hasOne(Student::class,'id','studentProfileId');
    }

    /**
     * @return mixed
     */
    public function responderProfile()
    {
        return $this->hasOne(Responder::class,'id','responderProfileId');
    }

    public function checkBothUser2($request){
        if($this->isSlotAvailable2($request,$this->studentProfileId,'studentProfileId') || $this->isSlotAvailable2($request,$this->responderProfileId,'responderProfileId'))
            return true;

        return false;
    }
    public function isSlotAvailable2($request,$tId,$userType){
        
        $totalCount=0;
        foreach ($request as $req) {
            $startDate  = date('Y-m-d H:i:s',strtotime($req['startDate']));
            $endDate    = date('Y-m-d H:i:s',strtotime($req['endDate']));
            //$rEndDate   = date('Y-m-d H:i:s',strtotime($request->get('rEndDate')));

            $isNever = ScheduleSessions2::where(function($query) use ($startDate,$endDate) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','>=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('startDate','<',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','<=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','>',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('endDate','>',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','<',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','>=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','<=',date('Y-m-d H:i:s',strtotime($endDate)));
                            });
                    });
                    //->where('type','=',ScheduleSessions2::TYPE_NEVER);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->where('isDeleted','=',0)
            ->where('status','=',ScheduleSessions2::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();

            $totalCount = $totalCount + $isNever;
        }
        

        return ($totalCount > 0 ) ? true : false;
    }
    public function checkBothUser($request){
        if($this->isSlotAvailable($request,$this->studentProfileId,'studentProfileId') || $this->isSlotAvailable($request,$this->responderProfileId,'responderProfileId'))
            return true;

        return false;
    }
    public function checkBothUserU($request,$scheduleIds){
        if($this->isSlotAvailableU($request,$this->studentProfileId,'studentProfileId',$scheduleIds) || $this->isSlotAvailableU($request,$this->responderProfileId,'responderProfileId',$scheduleIds))
            return true;

        return false;
    }


    public function checkBothUserU2($request,$scheduleIds){
        if($this->isSlotAvailableU2($request,$this->studentProfileId,'studentProfileId',$scheduleIds) || $this->isSlotAvailableU2($request,$this->responderProfileId,'responderProfileId',$scheduleIds))
            return true;

        return false;
    }

    public function isSlotAvailableU2($request,$tId,$userType,$scheduleIds){
        
        $totalCount=0;
        foreach ($request as $req) {
            $startDate  = date('Y-m-d H:i:s',strtotime($req['startDate']));
            $endDate    = date('Y-m-d H:i:s',strtotime($req['endDate']));
            //$rEndDate   = date('Y-m-d H:i:s',strtotime($request->get('rEndDate')));

            $isNever = ScheduleSessions2::where(function($query) use ($startDate,$endDate,$scheduleIds) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','>=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('startDate','<',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','<=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','>',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('endDate','>',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','<',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','>=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','<=',date('Y-m-d H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('isDeleted','!=',0)
                    ->where('sessionId','!=',$scheduleIds);
                    //->where('type','=',ScheduleSessions2::TYPE_NEVER);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->where('status','=',ScheduleSessions2::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();

            $totalCount = $totalCount + $isNever;
        }
        

        return ($totalCount > 0 ) ? true : false;
    }

    public function isSlotAvailable($request,$tId,$userType){
        $startDate  = date('Y-m-d H:i:s',strtotime($request->get('startDateTime')));
        $endDate    = date('Y-m-d H:i:s',strtotime($request->get('endDateTime')));
        $rEndDate   = date('Y-m-d H:i:s',strtotime($request->get('rEndDate')));
        
        //Case Never
        // $isNever = ScheduleSessions::where(function($query) use ($startDate,$endDate) {
        //         return $query->whereBetween('startDate', [$startDate, $endDate])
        //             ->orwhereBetween('endDate', [$startDate, $endDate]);
        //     })
        //     // ->where('type','=',ScheduleSessions::TYPE_NEVER)
        //     ->where('status','=',ScheduleSessions::ACTIVE)
        //     ->where($userType,'=',$tId)
        //     ->count();
        $isNever = ScheduleSessions::where(function($query) use ($startDate,$endDate) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','>=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('startDate','<',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','<=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','>',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('endDate','>',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','<',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','>=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','<=',date('Y-m-d H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('type','=',ScheduleSessions::TYPE_NEVER);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->where('status','=',ScheduleSessions::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();

        //Case Daily
        $isDaily = ScheduleSessions::where(function($query) use ($startDate,$endDate) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::REPEATED)
                    ->where('type','=',ScheduleSessions::TYPE_DAILY);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->orWhere(function($query) use ($startDate,$endDate) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate){
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::NOT_REPEATED)
                    ->where('rEndDate','>',$startDate)
                    ->where('type','=',ScheduleSessions::TYPE_DAILY);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->where('status','=',ScheduleSessions::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();

        //Cover Weekly Scenario
        $isWeekly = ScheduleSessions::where(function($query) use ($startDate,$endDate) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::REPEATED)
                    ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->orWhere(function($query) use ($startDate,$endDate) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate){
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::NOT_REPEATED)
                    ->where('rEndDate','>',$startDate)
                    ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })                        
            // ->where('type','=',ScheduleSessions::TYPE_DAILY)
            ->where('status','=',ScheduleSessions::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();


        //Cover Monthly Scenario
        $isMonthly = ScheduleSessions::where(function($query) use ($startDate,$endDate) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::REPEATED)
                    ->where(DB::raw("DAYOFMONTH(startDate)"), date('d',strtotime($startDate)));
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->orWhere(function($query) use ($startDate,$endDate) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate){
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::NOT_REPEATED)
                    ->where('rEndDate','>',$startDate)
                    ->where(DB::raw("DAYOFMONTH(startDate)"), date('d',strtotime($startDate)));
            })                        
            // ->where('type','=',ScheduleSessions::TYPE_DAILY)
            ->where('status','=',ScheduleSessions::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();

        return ($isNever > 0 || $isDaily > 0 || $isWeekly > 0 || $isMonthly > 0) ? true : false;
    }
    public function isSlotAvailableU($request,$tId,$userType,$scheduleIds){
        $startDate  = date('Y-m-d H:i:s',strtotime($request->get('startDateTime')));
        $endDate    = date('Y-m-d H:i:s',strtotime($request->get('endDateTime')));
        $rEndDate   = date('Y-m-d H:i:s',strtotime($request->get('rEndDate')));
        //Case Never
        // $isNever = ScheduleSessions::where(function($query) use ($startDate,$endDate) {
        //         return $query->whereBetween('startDate', [$startDate, $endDate])
        //             ->orwhereBetween('endDate', [$startDate, $endDate]);
        //     })
        //     // ->where('type','=',ScheduleSessions::TYPE_NEVER)
        //     ->where('status','=',ScheduleSessions::ACTIVE)
        //     ->where($userType,'=',$tId)
        //     ->whereNotIn('id',[$scheduleIds])
        //     ->count();

        $isNever = ScheduleSessions::where(function($query) use ($startDate,$endDate,$scheduleIds) {
                return $query->where(function($query) use ($startDate, $endDate,$scheduleIds){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','>=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('startDate','<',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','<=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','>',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('endDate','>',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','<',date('Y-m-d H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->where('startDate','>=',date('Y-m-d H:i:s',strtotime($startDate)))
                                ->where('endDate','<=',date('Y-m-d H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('type','=',ScheduleSessions::TYPE_NEVER)
                    ->whereNotIn('id',[$scheduleIds]);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->where('status','=',ScheduleSessions::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();
            
        //Case Daily
        $isDaily = ScheduleSessions::where(function($query) use ($startDate,$endDate,$scheduleIds) {
                return $query->where(function($query) use ($startDate, $endDate,$scheduleIds){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::REPEATED)
                    ->where('type','=',ScheduleSessions::TYPE_DAILY)
                    ->whereNotIn('id',[$scheduleIds]);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->orWhere(function($query) use ($startDate,$endDate,$scheduleIds) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate){
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::NOT_REPEATED)
                    ->where('rEndDate','>',$startDate)
                    ->where('type','=',ScheduleSessions::TYPE_DAILY)
                    ->whereNotIn('id',[$scheduleIds]);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->where('status','=',ScheduleSessions::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();

        //Cover Weekly Scenario
        $isWeekly = ScheduleSessions::where(function($query) use ($startDate,$endDate,$scheduleIds) {
                return $query->where(function($query) use ($startDate, $endDate,$scheduleIds){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::REPEATED)
                    ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1)
                    ->whereNotIn('id',[$scheduleIds]);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->orWhere(function($query) use ($startDate,$endDate,$scheduleIds) {
                return $query->where(function($query) use ($startDate, $endDate){
                        return $query->where(function ($query) use ($startDate, $endDate){
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::NOT_REPEATED)
                    ->where('rEndDate','>',$startDate)
                    ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1)
                    ->whereNotIn('id',[$scheduleIds]);
            })                        
            // ->where('type','=',ScheduleSessions::TYPE_DAILY)
            ->where('status','=',ScheduleSessions::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();


        //Cover Monthly Scenario
        $isMonthly = ScheduleSessions::where(function($query) use ($startDate,$endDate,$scheduleIds) {
                return $query->where(function($query) use ($startDate, $endDate,$scheduleIds){
                        return $query->where(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::REPEATED)
                    ->where(DB::raw("DAYOFMONTH(startDate)"), date('d',strtotime($startDate)))
                    ->whereNotIn('id',[$scheduleIds]);
                    // ->where(DB::raw("DAYOFWEEK(startDate)"), date('w',strtotime($startDate)) + 1);
            })
            ->orWhere(function($query) use ($startDate,$endDate,$scheduleIds) {
                return $query->where(function($query) use ($startDate, $endDate,$scheduleIds){
                        return $query->where(function ($query) use ($startDate, $endDate){
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('startDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','<=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','>',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('endDate','>',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<',date('H:i:s',strtotime($endDate)));
                            })
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                return $query->whereTime('startDate','>=',date('H:i:s',strtotime($startDate)))
                                ->whereTime('endDate','<=',date('H:i:s',strtotime($endDate)));
                            });
                    })
                    ->where('repeated','=',ScheduleSessions::NOT_REPEATED)
                    ->where('rEndDate','>',$startDate)
                    ->where(DB::raw("DAYOFMONTH(startDate)"), date('d',strtotime($startDate)))
                    ->whereNotIn('id',[$scheduleIds]);
            })                        
            // ->where('type','=',ScheduleSessions::TYPE_DAILY)
            ->where('status','=',ScheduleSessions::ACTIVE)
            ->where($userType,'=',$tId)
            ->count();

        return ($isNever > 0 || $isDaily > 0 || $isWeekly > 0 || $isMonthly > 0) ? true : false;
    }
}
