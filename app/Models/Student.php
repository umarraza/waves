<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
	//Status Constants
	const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'student_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userId',
        'studentId',
        'schoolProfileId',
        'firstName',
        'lastName',
        'gradeLevel',
        'authorizationCode'
    ];

    /**
     * @return mixed
     */
    public function schoolProfile()
    {
        return $this->hasOne(SchoolProfiles::class,'id','schoolProfileId');
    }
    

    public function fullName(){
        return $this->firstName.' '.$this->lastName;
    }
    
    /**
     * @return mixed
     */
    public function user()
    {
        return $this->hasOne(User::class,'id','userId');
    }

    public function getResponseData(){
        return [
            'userId'        => $this->userId,
            'firstName'     => $this->firstName,
            'lastName'      => $this->lastName,
            'fullName'      => $this->firstName.' '.$this->lastName,
            'username'      => $this->user->username,
            'roles'         =>  [
                                    'id'        => $this->user->roleId,    
                                    'label'     => $this->user->role->label,
                                ],
            'verified'      => $this->user->statusVerified(),
            'studentId'     => $this->studentId,
            'gradeLevel'     => $this->gradeLevel,
            'authorizationCode'     => $this->authorizationCode,
            'onlineStatus'      => $this->user->onlineStatus,
            'image'      => $this->user->getImage()
        ];
    }

}
