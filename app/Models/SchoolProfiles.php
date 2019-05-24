<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class SchoolProfiles extends Model
{
    //Attributes Constants
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'school_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'accessCode',
        'schoolName',
        'schoolAddress',
        'schoolLogo',
        'schoolTimeZone',
        'schoolTimeValue',
        'schoolTimeSymbol',
        'schoolTimeArea',
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
    public function studentProfiles()
    {
        return $this->hasOne(StudentProfiles::class,'id','schoolProfileId');
    }

    /**
     * @return mixed
     */
    public function schoolAdminProfile()
    {
        return $this->hasOne(SchoolAdminProfiles::class,'schoolProfileId','id');
    }


    public function schoolSecondaryAdminProfile()
    {
        return $this->hasOne(SchoolSecondaryAdminProfile::class,'schoolProfileId','id');
    }
}
