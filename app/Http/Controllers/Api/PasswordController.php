<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use JWTAuthException;
use JWTAuth;

use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdminProfiles;
use App\Models\Api\ApiSchoolSecondaryAdminProfile as SchoolSecondaryAdminProfile;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiStudent as Student;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Hash;
use Carbon\Carbon;
use DateTime;


class PasswordController extends Controller
{
    public function changePasswordSignUp(Request $request)
    {
    	$request['userId'] = (int)base64_decode($request->userId);
    	$request['newPassword'] = base64_decode($request->newPassword);
    	$request['token'] = base64_decode($request->token);

	  	$response = [
	      	'data' => [
	          	'code' => 400,
	         	'message' => 'Something went wrong. Please try again later!',
	      	],
	     	'status' => false
	  	];
	  	$rules = [
	     	'userId' 		=> 	'required',
	     	'newPassword' 	=>	'required|string|min:5',
	     	'token' 		=>	'required',
	  	];
	  	$validator = Validator::make($request->all(), $rules);
	  	if ($validator->fails()) {
	    	$response['data']['message'] = 'Invalid input values.';
	      	$response['data']['errors'] = $validator->messages();
	  	}else
	  	{

	      	$user= User::find($request->userId);

	      	if(!empty($user) && $user->statusVerified())
	      	{
				if($user->resetPasswordToken==$request->token)
				{
					$start  = new DateTime($user->createdResetPToken);
					$end    = new DateTime(); //Current date time
					$diff   = $start->diff($end);

					if($diff->d<=0)
					{
						$user =  User::where('id', $request->get('userId'))
							            ->update([
							            	'password' => bcrypt($request->get('newPassword')),
							            	'verified' => '1',
							            	'resetPasswordToken' => Null,
							            ]); 
						if ($user) 
						{
							$response['data']['message'] = 'Request Successfull';
							$response['data']['code'] = 200;
							$response['status'] = true;
						}
					}
					else
					{
						$response['data']['message'] = 'Token expired';
						$response['data']['code'] = 400;
						$response['status'] = true;
					}

				}
				else
				{
					$response['data']['message'] = 'Invalid Token';
					$response['data']['code'] = 400;
					$response['status'] = true;
				}
			}
			else
			{
				$response['data']['message'] = 'User Does not exist';
				$response['data']['code'] = 400;
				$response['status'] = true;
			}
	  	}
		return $response;
    }

    public function changePassword(Request $request)
    {
    	$request['userId'] = (int)base64_decode($request->userId);
    	$request['oldPassword'] = base64_decode($request->oldPassword);
    	$request['newPassword'] = base64_decode($request->newPassword);
    	$user = JWTAuth::toUser($request->token);
		$response = [
		      'data' => [
		          'code'      => 400,
		          'error'     => '',
		          'message'   => 'Invalid Token! User Not Found.',
		      ],
		      'status' => false
		  ];
		if(!empty($user) && $user->statusVerified())
		{
		  	$response = [
		      	'data' => [
		          	'code' => 400,
		         	'message' => 'Something went wrong. Please try again later!',
		      	],
		     	'status' => false
		  	];
		  	$rules = [
		     	'userId' 		=> 	'required',
		     	'oldPassword' 	=>	'required|string|min:5',
		     	'newPassword' 	=>	'required|string|min:5',
		  	];
		  	$validator = Validator::make($request->all(), $rules);
		  	if ($validator->fails()) {
		    	$response['data']['message'] = 'Invalid input values.';
		      	$response['data']['errors'] = $validator->messages();
		  	}else
		  	{
		  		$oldPassword = $request->oldPassword;
		  		$newPassword = $request->newPassword;

		      	$user= User::find($request->userId);

		      	if(!empty($user))
		      	{
		      		if(Hash::check($oldPassword, $user->password))
		      		{
		      			if($oldPassword != $newPassword)
		      			{
				      		$user =  User::where('id', $request->get('userId'))
								            ->update([
								            	'password' => bcrypt($newPassword),
								            ]); 
							if ($user) 
							{
								$response['data']['message'] = 'Request Successfull';
								$response['data']['code'] = 200;
								$response['status'] = true;
							}
						}
						else
						{
							$response['data']['message'] = 'Request Successfull';
							$response['data']['code'] = 401;
							$response['status'] = true;
						}
					}
					else
					{
						$response['data']['message'] = 'Your Old Password does not matach';
						$response['data']['code'] = 400;
						$response['status'] = true;
					}
				}
				else
				{
					$response['data']['message'] = 'User Does not exist';
					$response['data']['code'] = 400;
					$response['status'] = true;
				}
		  	}
		}
		return $response;
    }

    public function forgotPassword(Request $request)
    {

    	$response = [
	      	'data' => [
	          	'code' => 400,
	         	'message' => 'Something went wrong. Please try again later!',
	      	],
	     	'status' => false
		];
	  	$rules = [
	     	'username' => 'required',
	  	];
	  	$validator = Validator::make($request->all(), $rules);
	  	if ($validator->fails()) {
	    	$response['data']['message'] = 'Invalid input values.';
	      	$response['data']['errors'] = $validator->messages();
	  	}else
	  	{
    		$user= User::where('username','=',$request->username)
    					->where('isDeleted','=',0)
    					->first();
    		$name = "null";
    		//return $user;
    		
	      	if(!empty($user))
	      	{
	      		if($user->verified)
		      	{
		      		if($user->roleId==2)
		      		{
		      			//$name=SchoolAdminProfiles::select('firstName')->where('userId','=',$user->id)->first();
		      			$name = $user->schoolAdminProfile->firstName;
		      		}
		      		elseif($user->roleId==3)
		      		{
		      			//$name=Responder::select('firstName')->where('userId','=',$user->id)->first();
		      			$name = $user->responder->firstName;
		      		}
		      		else if($user->roleId==4)
		      		{
		      			//$name=Student::select('firstName')->where('userId','=',$user->id)->first();
		      			$name = $user->student->firstName;
		      		}
		      		else if($user->roleId==5)
		      		{
		      			//$name=SchoolSecondaryAdminProfile::select('firstName')->where('userId','=',$user->id)->first();
		      			$name = $user->schoolSecondaryAdminProfile->firstName;
		      		}
		      		else
		      		{
		      			$name="Super Admin";
		      		}

		      		if($name!="Super Admin")
		      		{
			      		//$token = bcrypt(rand ( 10000 , 100000 ));
			      		$token = md5(time() . $user->id . 'waves');
			      		$user->resetPasswordToken = $token;

			      		$now = Carbon::now();//->format('Y-m-d H:i:s');
			      		$user->createdResetPToken = $now;

						if ($user->save() && $user->sendEmailForgotPassword($token,$name)) 
						{
							$response['data']['message'] = 'If this email matches our records, we will email you password reset instructions.';
							$response['data']['code'] = 200;
							$response['status'] = true;
						}
					}
					else
					{
						$response['data']['message'] = 'If this email matches our records, we will email you password reset instructions.';
						$response['data']['code'] = 400;
						$response['status'] = true;
					}
					
				}
				else
				{
					$response['data']['message'] = 'If this email matches our records, we will email you password reset instructions.';
					$response['data']['code'] = 400;
					$response['status'] = true;
				}				
			}
			else
			{
				$response['data']['message'] = 'If this email matches our records, we will email you password reset instructions.';
				$response['data']['code'] = 400;
				$response['status'] = true;
			}
		}
		return $response;
	}
		
}
