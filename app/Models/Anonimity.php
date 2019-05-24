<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anonimity extends Model
{
    //Status Constants
	const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'anonimity_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'studentProfileId',
        'responderId',
        'studentId',
        'threadId',
        'message',
        'comment',
        'schoolProfileId',
        'localCreatedAt',
    ];



    /**
     * @return mixed
     */
    public function responderProfile()
    {
        return $this->hasOne(Responder::class,'id','responderId');
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','responderId');
    }
}
