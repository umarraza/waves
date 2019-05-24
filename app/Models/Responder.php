<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responder extends Model
{
    //Status Constants
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const POS_COUNCELAR = 'counselor';
    const POS_PSYCHOLOGIST = 'Psychologist';
    const POS_SOCIAL_WORKER = 'social worker';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'responder_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'firstName',
        'lastName',
        'responderId',
        'userId',
        'position',
        'schoolProfileId',
        'authorizationCode',
        'isAvalable',
    ];

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->hasOne(User::class,'id','userId');
    }

    /**
     * @return mixed
     */
    public function schoolAdminProfile()
    {
        return $this->hasOne(SchoolAdminProfiles::class,'id','schoolAdminProfilesId');
    }


    public function schoolProfile()
    {
        return $this->hasOne(SchoolProfiles::class,'id','schoolProfileId');
    }

    public function fullName(){
        return $this->firstName.' '.$this->lastName;
    }

    public function isCounselor(){
        if(strtolower($this->position) == self::POS_COUNCELAR)
            return true;

        return false;
    }

    public function getCategory(){
        return $this->hasOne(ResponderCategory::class,'id','position');
    }

    public function getResponseData(){
        return [
            'userId'            => $this->userId,
            'firstName'         => $this->firstName,
            'lastName'          => $this->lastName,
            'username'          => $this->user->username,
            'roles'             =>  [
                                        'id'        => $this->user->roleId,    
                                        'label'     => $this->user->role->label,
                                    ],
            'verified'          => $this->user->statusVerified(),
            'responderId'       => $this->responderId,
            'position'          => $this->getCategory->positionName,
            'title'             => $this->title,
            'authorizationCode' => $this->authorizationCode,
            'isAvalable'        => $this->isAvalable,
            'onlineStatus'      => $this->user->onlineStatus,
            'image'             => $this->user->getImage(),
            'levelId'           => $this->getCategory->levelId,
        ];
    }
}
