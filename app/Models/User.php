<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Components\PushNotification;

class User extends Authenticatable
{
    //Status Constants
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = 1;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const USER_SUPER_ADMIN = 'super_admin';
    const USER_ADMIN = 'admin';
    const USER_RESPONDER = 'responder';
    const USER_STUDENT = 'student';
    const USER_SECONDARY_ADMIN = 'secondary_admin';
    
    use Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'roleId',
        'resetPasswordToken',
        'createdResetPToken',
        'avatarFilePath',
        'deviceToken',
        'deviceType',
        'verified',
        'onlineStatus',
        'isDeleted',
        // 'createdAt',
        // 'updatedAt'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'resetPasswordToken','deviceToken',
    ];

    /**
     * The dynamic attributes from mutators that should be returned with the user object.
     * @var array
     */
    protected $appends = ['full_name'];

    /**
     * @return mixed
     */
    public function role()
    {
        return $this->hasOne(Roles::class,'id','roleId');
    }

    /**
     * @return mixed
     */
    public function schoolAdminProfile()
    {
        return $this->hasOne(SchoolAdminProfiles::class,'userId','id');
    }

    /**
     * @return mixed
     */
    public function schoolSecondaryAdminProfile()
    {
        return $this->hasOne(SchoolSecondaryAdminProfile::class,'userId','id');
    }

    /**
     * @return mixed
     */
    public function Responder()
    {
        return $this->hasOne(Responder::class,'userId','id');
    }

    /**
     * @return mixed
     */
    public function DeviceToken()
    {
        return $this->hasMany(DeviceToken::class,'userId','id');
    }

    /**
     * @return mixed
     */
    public function Student()
    {
        return $this->hasOne(Student::class,'userId','id');
    }

    public function statusVerified(){
        return ($this->verified) ? 'Active' : 'In-Active';
    }

    public function isSuperAdmin(){
        if($this->role->label == self::USER_SUPER_ADMIN)
            return true;

        return false;
    }

    public function isAdmin(){
        if($this->role->label == self::USER_ADMIN)
            return true;

        return false;
    }

    public function isSecondaryAdmin(){
        if($this->role->label == self::USER_SECONDARY_ADMIN)
            return true;

        return false;
    }

    public function isResponder(){
        if($this->role->label == self::USER_RESPONDER)
            return true;

        return false;
    }

    public function isStudent(){
        if($this->role->label == self::USER_STUDENT)
            return true;

        return false;
    }

    public function isVerified(){
        if($this->verified == self::STATUS_ACTIVE)
            return true;

        return false;
    }

    public function isUserDeleted(){
        if($this->isDeleted == self::STATUS_DELETED)
            return true;

        return false;
    }

    public function isValidUser(){
        if($this->isSuperAdmin())
            return true;
        elseif($this->isAdmin() && !empty($this->schoolAdminProfile) && !($this->isUserDeleted()))
            return true;
        elseif($this->isResponder() && !empty($this->responder) && !($this->isUserDeleted()))
            return true;
        elseif($this->isStudent() && !empty($this->student) && !($this->isUserDeleted()))
            return true;
        elseif($this->isSecondaryAdmin() && !empty($this->schoolSecondaryAdminProfile) && !($this->isUserDeleted()))
            return true;
        return false;
    }

    public static function getTypes(){
        return [
                self::USER_SUPER_ADMIN => 'Super Admin', 
                self::USER_ADMIN => 'Admin',
                self::USER_RESPONDER => 'Responder',
                self::USER_STUDENT => 'Student'
            ];
    }
    
    public function getArrayResponse() {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'verified'      => $this->statusVerified(),
            'roles' => [
                'id'        => $this->roleId,    
                'label'     => $this->role->label,
            ],
            'image'         => $this->getImage(),
            'onlineStatus'  => $this->onlineStatus,
            'name'          => $this->getName(),
            'userId'        => $this->id,
            'title'         => $this->getTitle(),
            'firstName'     => $this->getFirstName(),
            'lastName'      => $this->getLastName(),
        ];
    }

    public function getName(){
        if($this->isAdmin() && !empty($this->schoolAdminProfile))
            return $this->schoolAdminProfile->firstName.' '.$this->schoolAdminProfile->lastName;
        elseif($this->isResponder() && !empty($this->responder))
            return $this->responder->firstName.' '.$this->responder->lastName;
        elseif($this->isStudent() && !empty($this->student))
            return $this->student->firstName.' '.$this->student->lastName;

        return '';
    }

    public function getFullNameAttribute(){
        if($this->isAdmin() && !empty($this->schoolAdminProfile))
            return $this->schoolAdminProfile->firstName.' '.$this->schoolAdminProfile->lastName;
        elseif($this->isResponder() && !empty($this->responder))
            return $this->responder->firstName.' '.$this->responder->lastName;
        elseif($this->isStudent() && !empty($this->student))
            return $this->student->firstName.' '.$this->student->lastName;

        return '';
    }

    public function getFirstName(){
        if($this->isAdmin() && !empty($this->schoolAdminProfile))
            return $this->schoolAdminProfile->firstName;
        elseif($this->isResponder() && !empty($this->responder))
            return $this->responder->firstName;
        elseif($this->isStudent() && !empty($this->student))
            return $this->student->firstName;

        return '';
    }

    public function getLastName(){
        if($this->isAdmin() && !empty($this->schoolAdminProfile))
            return $this->schoolAdminProfile->lastName;
        elseif($this->isResponder() && !empty($this->responder))
            return $this->responder->lastName;
        elseif($this->isStudent() && !empty($this->student))
            return $this->student->lastName;

        return '';
    }
    public function getTitle(){
        if($this->isResponder() && !empty($this->responder))
            return $this->responder->title;
        else
            return '';
    }
    
    public function getDefaultImage(){
        $defaultImage = '';//'dumy.png';
        // if(file_exists(storage_path('app/public/'.$defaultImage)))
            return $defaultImage;

        // return '';
    }

    public function getImage(){
        // if(!empty($this->avatarFilePath) && file_exists(public_path($this->avatarFilePath)))
        //     return url('public/'.$this->avatarFilePath);
        // return '';
        if(!empty($this->avatarFilePath))
            return $this->avatarFilePath;

        return $this->getDefaultImage();
    }

    public function clearDeviceToken(){
        if(!empty($this->deviceToken)){
            $this->update([
                'deviceToken' => ''
            ]);
        }
    }

    // public function sendPushNotification($message,$screenType){
    //     if(!empty($message) && !empty($this->deviceToken)){
    //         $data = [
    //             'message'           =>  $message,
    //             'messagetemp'       =>  $screenType.$message,
    //             'screenType'        =>  $screenType,
    //             'registrationID'    =>  $this->deviceToken
    //         ];
    //          PushNotification::send($this->deviceType,$data,$screenType);
    //         return true;
    //     }
    //     return false;
    // }

    public function sendPushNotification($message,$screenType){
    	//$deviceTokens = DeviceToken::where('userId','=',$this->id)->get();
    	foreach ($this->DeviceToken as  $value) {
    	
	        if(!empty($message) && !empty($value->deviceToken)){
	            $data = [
	                'message'           =>  $message,
	                'messagetemp'       =>  $screenType.$message,
	                'screenType'        =>  $screenType,
	                'registrationID'    =>  $value->deviceToken,
	            ];
	             PushNotification::send($value->deviceType,$data,$screenType);
	        }
	    }
        return true;
    }

    public function sendSessionMail($sessionMessage,$firstName,$mailSubject){
        \Mail::send('vendor.mail.html.waves.sessions', [
                'firstName' => $firstName,
                'sessionMessage'   => $sessionMessage,
            ], function ($message) use ($mailSubject){
                $message->from('hello@abovethewaves.co', 'The Waves Team');
                $message->to($this->username)->subject($mailSubject);
        });

        return true;
    }
}
