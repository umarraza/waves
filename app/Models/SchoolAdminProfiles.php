<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class SchoolAdminProfiles extends Model
{
    //Attributes Constants
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'school_admin_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userId',
        'schoolProfileId',
        'firstName',
        'lastName'
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
    public function user()
    {
        return $this->hasOne(User::class,'id','userId');
    }

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

    public function sendEmail($password){
        \Mail::send('vendor.mail.html.waves.default', ['name' => $this->firstName,'accessCode' => $this->schoolProfile->accessCode,'password' => $password], function ($message) {
            $message->from('hello@abovethewaves.co', 'The Waves Team');

            $message->to($this->user->username)->subject('The Waves App | Welcome');
        });

        return true;
    }

    public function getResponseData(){
        return [
            'schoolId'      => $this->id,
            'firstName'     => $this->firstName,
            'lastName'      => $this->lastName,
            'username'      => $this->user->username,
            'accessCode'    => $this->schoolProfile->accessCode,
            'schoolName'    => $this->schoolProfile->schoolName,
            'schoolTimeZoneNew'=> $this->schoolProfile->schoolTimeSymbol."@".$this->schoolProfile->schoolTimeValue."@".$this->schoolProfile->schoolTimeArea,
            'schoolTimeZone'=> $this->schoolProfile->schoolTimeArea,
            'schoolTimeSymbol'=> $this->schoolProfile->schoolTimeSymbol,
            'schoolTimeValue'=> $this->schoolProfile->schoolTimeValue,
            'schoolAddress' => $this->schoolProfile->schoolAddress,
            'schoolLogo'    => $this->schoolProfile->schoolLogo,
            'onlineStatus'  => $this->user->onlineStatus,
            'image'         => $this->user->getImage(),
            'fullName'      => $this->fullName(),
        ];
    }
}
