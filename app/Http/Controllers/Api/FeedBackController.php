<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiStudent as Student;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdmin;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use JWTAuthException;
use JWTAuth;

class FeedBackController extends Controller
{
    public function sendFeedback(Request $request)
    {
  		$user = JWTAuth::toUser($request->token);
		$response = [
		      'data' => [
		          'code'      => 400,
		          'error'     => '',
		          'message'   => 'Invalid Token! User Not Found.',
		      ],
		      'status' => false
		  ];
		if(!empty($user))
		{

		  	$response = [
		      	'data' => [
		          	'code' => 400,
		         	'message' => 'Something went wrong. Please try again later!',
		      	],
		     	'status' => false
		  	];
		  	$rules = [
		     	'userId' => 'required',
		     	'text'	=> 'required',
		  	];
		  	$validator = Validator::make($request->all(), $rules);
		  	if ($validator->fails()) {
		    	$response['data']['message'] = 'Invalid input values.';
		      	$response['data']['errors'] = $validator->messages();
		  	}else
		  	{

		      	$user= User::find($request->userId);

		      	if(!empty($user))
		      	{
		      		if($user->role->label== User::USER_STUDENT)
		      		{
		      			$studentProfile = Student::where('userId',$request->userId)->first();

		      			if($studentProfile->sendEmailFeedback($request->text))
		      			{
		      				$response['data']['message'] = 'Request Successfull';
							$response['data']['code'] = 200;
							$response['status'] = true;
		      			}
		      		}
		      		else if($user->role->label== User::USER_RESPONDER)
		      		{
		      			$responderProfile = Responder::where('userId',$request->userId)->first();
		      			
		      			if($responderProfile->sendEmailFeedback($request->text))
		      			{
		      				$response['data']['message'] = 'Request Successfull';
							$response['data']['code'] = 200;
							$response['status'] = true;
		      			}
		      		}
		      		else if($user->role->label== User::USER_ADMIN)
		      		{
		      			$adminProfile = SchoolAdmin::where('userId',$request->userId)->first();
		      			
		      			if($adminProfile->sendEmailFeedback($request->text))
		      			{
		      				$response['data']['message'] = 'Request Successfull';
							$response['data']['code'] = 200;
							$response['status'] = true;
		       			}
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
}
