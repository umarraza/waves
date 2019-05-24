<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    //Attributes Constants
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const MESSAGE_UNREAD = 0;
    const MESSAGE_READ = 1;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_chat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fromUser',
        'toUser',
        'message',
        'threadId',
        'readtemp',
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
    public function thread()
    {
        return $this->hasOne(Threads::class,'id','threadId');
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
}
