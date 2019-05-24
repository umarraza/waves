<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Threads extends Model
{
    //Attributes Constants
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const TYPE_ANONYMOUS = 1;
    const TYPE_NON_ANONYMOUS = 0;
    const THREAD_LABEL = 'Anonymous';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_threads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fromUser',
        'toUser',
        'threadName',
        'threadLabel',
        'type',
        'anonimityFlag',
        'causeData',
        'level',
        'schoolProfileId'
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
    public function chats()
    {
        return $this->hasMany(Chat::class,'threadId','id');
    }

    /**
     * @return mixed
     */
    public function fUser()
    {
        return $this->hasOne(User::class,'id','fromUser');
    }

    /**
     * @return mixed
     */
    public function tUser()
    {
        return $this->hasOne(User::class,'id','toUser');
    }

    public static function uniqueThreadName($userId){
        return md5(time() . $userId);
    }

    public static function uniqueThreadLabel($fromUser,$toUser,$type){
        $count = Threads::where(function($query) use ($fromUser,$toUser,$type) {
                                        return $query->where('fromUser','=',$fromUser)
                                            ->where('toUser','=',$toUser)
                                            ->where('type','=',$type);
                                    })
                                 ->orWhere(function($query) use ($fromUser,$toUser,$type) {
                                        return $query->where('fromUser','=',$toUser)
                                            ->where('toUser','=',$fromUser)
                                            ->where('type','=',$type);
                                    })
                                 ->count();
        ++$count;
        $modelToUser = User::where(['id' => $toUser])->first();
        if(!empty($modelToUser))
            return $modelToUser->getFirstName().'_'.self::THREAD_LABEL.'_'.$count;

        return self::THREAD_LABEL.$count;
    }

    public function getArrayResponse(){
        return [
                'id'            => $this->id,
                'threadName'    => $this->threadName,
                'threadLabel'   => $this->threadLabel,
                'anonimity'     => $this->anonimityFlag,
                'level'         => $this->level,
                'type'          => ($this->type) ? 'Anonymous' : 'Normal',
                'createdAt'     => date('Y-m-d H:i:s',strtotime($this->createdAt)),
                'causeData'     => $this->causeData,
                'fromUserActiveStatus'      => $this->fUser->onlineStatus,
                'toUserActiveStatus'      => $this->tUser->onlineStatus,
                'toUserId'      => $this->tUser->id,
                'fromUserId'      => $this->fUser->id,
            ];
    }
}
