<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuthException;
use JWTAuth;
use App\Http\Requests;
use App\Models\Api\ApiUser as User;
use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiLocalResource as LocalResource;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdminProfiles;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;
use App\Models\Api\ApiCrisisResource as CrisisResource;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiStudent as Student;
use App\Models\InvoicesExport;
use App\Models\Api\ApiResponderCategory as ResponderCategory;
use App\Models\Api\ApiLevel as Level;
class ImportDataController extends Controller
{
    public function localResource(Request $request)
    {
        $request['jsonData'] = base64_decode($request->jsonData);
        $request['schoolProfileId'] = base64_decode($request->schoolProfileId);

        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'    => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified())
        { 
            $rules = [
                'jsonData'          => ['required','json'],
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
            ];
            $response['data']['code']=501;
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) 
            {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                //return $request->jsonData;
                $response['data']['code']=400;
                $jsonData = json_decode($request->get('jsonData'));
                //return $jsonData->LocalResource;
                $jsonData = $jsonData->localRes;

                $schoolProfileId = $request->schoolProfileId;
                
                $errorsData = [];

                $errorFieldMessage = "";
                $errorFieldStatus = false;
                
                $totalData=count($jsonData);
                
                $recordCount    = 0;
                $count          = 1;
                $savedCount     = 0;
                $errorCount     = 0;
                $updateCount    = 0;

               
                foreach ($jsonData as $attributes) 
                {
                	$errorFieldMessage = "";
                    $errorFieldStatus = false;
                    $value = (Array) $attributes;
                    // if($count==$totalData)
                    // {
                    //     break;
                    // }

                    // ================================ Required Checks =====================================//
                    if(!isset($value['name']) || $value['name']==null)
                    {
                        $value['name']= null;
                        $errorFieldStatus   = true;
                        $errorFieldMessage  = $errorFieldMessage."The name of the local resource is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['name']);
                    	$lengthLimit		= 50;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                        	$errorFieldMessage  = $errorFieldMessage."The local resource name is limited to ".$lengthLimit." characters";
                    	}
                    }
                    if(!isset($value['insuranceType']) || $value['insuranceType']==null)
                    {
                        $value['insuranceType'] = null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Insurance type(s) is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Insurance type(s) is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['insuranceType']);
                    	$lengthLimit		= 1000;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Insurance type(s) is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Insurance type(s) is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['serviceTypeId']) || $value['serviceTypeId']==null)
                    {
                        $value['serviceTypeId'] = null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Description of service(s) is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Description of service(s) is required";
                    }
                    else
                    {
                        $lengthChecker      = strlen($value['serviceTypeId']);
                        $lengthLimit        = 1000;
                        if($lengthChecker>$lengthLimit)
                        {
                            $errorFieldStatus   = true;
                            if($errorFieldMessage!="")
                                $errorFieldMessage  = $errorFieldMessage.", Description of service(s) is limited to ".$lengthLimit." characters";
                            else
                                $errorFieldMessage  = $errorFieldMessage."Description of service(s) is limited to ".$lengthLimit." characters";
                        }
                    }

                    if(!isset($value['streetAddress']) || $value['streetAddress']==null)
                    {
                        $value['streetAddress'] = null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Street address is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Street address is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['streetAddress']);
                    	$lengthLimit		= 100;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Street address is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Street address is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['city']) || $value['city']==null)
                    {
                        $value['city']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", City is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."City is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['city']);
                    	$lengthLimit		= 20;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", City name is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."City name is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['state']) ||  $value['state']==null)
                    {
                        $value['state']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", State is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."State is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['state']);
                    	$lengthLimit		= 20;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", State name is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."State name is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['zipCode']) || $value['zipCode']==null)
                    {
                        $value['zipCode']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Zip code is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Zip code is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['zipCode']);
                    	$lengthLimit		= 5;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Zip code is limited to ".$lengthLimit." digits";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Zip code is limited to ".$lengthLimit." digits";
                    	}

                        if(!is_numeric($value['zipCode']))
                        {
                            $errorFieldStatus   = true;
                            if($errorFieldMessage!="")
                                $errorFieldMessage  = $errorFieldMessage.", Zip code must only contain numbers";
                            else
                                $errorFieldMessage  = $errorFieldMessage."Zip code must only contain numbers";
                        }
                    }
                    if(!isset($value['website']) || $value['website']==null)
                    {
                        $value['website']=null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Website is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Website is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['website']);
                    	$lengthLimit		= 200;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Website domain is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Website domain is limited to ".$lengthLimit." characters";
                    	}

                    	if(!filter_var($value['website'], FILTER_VALIDATE_URL))
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Please enter a valid website";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Please enter a valid website";
                    	}
                    	
                    }

                    if(!isset($value['phoneNumber']) || $value['phoneNumber']==null)
                    {
                        $value['phoneNumber']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Phone number is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Phone number is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['phoneNumber']);
                    	$lengthLimit		= 15;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Phone number length limit is ".$lengthLimit." digits";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Phone number is limited to ".$lengthLimit." digits";
                    	}

                    	if(!is_numeric($value['phoneNumber']))
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Please enter a valid phone number";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Please enter a valid phone number";
                    	}
                    }

                    // =========================== Required Checks Ends ================================//

                    if($errorFieldStatus==false)
                    {
                        $localResource = LocalResource::where('schoolProfileId','=',$schoolProfileId)
                                                        ->where('name','=',$value['name'])
                                                        ->count();
                        if ($localResource>0) 
                        {
                            $localResource = LocalResource::where('name','=',$value['name'])
                                                            ->where('schoolProfileId','=',$schoolProfileId)
                                                            ->update($value);
                            $updateCount++;
                        }
                        else
                        {
                            $localResource = LocalResource::create([
                                                                        'name'              => $value['name'],
                                                                        'insuranceType'   => $value['insuranceType'],
                                                                        'streetAddress'   => $value['streetAddress'],
                                                                        'city'          => $value['city'],
                                                                        'state'         => $value['state'],
                                                                        'zipCode'       => $value['zipCode'],
                                                                        'phoneNumber'     => $value['phoneNumber'],
                                                                        'website'       => $value['website'],
                                                                        'schoolProfileId'   => $schoolProfileId,
                                                                        'serviceTypeId'     => $value['serviceTypeId'],
                                                                    ]);
                            $savedCount++;
                        }
                    }
                    else
                    {
                        $recordCount++;
                        $errorCount++;
                        $errorsData[$recordCount]['Name']               = $value['name'];
                        $errorsData[$recordCount]['Description Of Service(s)']  = $value['serviceTypeId'];
                        $errorsData[$recordCount]['Insurance Type(s)']  = $value['insuranceType'];
						$errorsData[$recordCount]['Phone Number']       = $value['phoneNumber'];
						$errorsData[$recordCount]['Website']            = $value['website'];
                        $errorsData[$recordCount]['Street Address']     = $value['streetAddress'];
                        $errorsData[$recordCount]['City']               = $value['city'];
                        $errorsData[$recordCount]['State']              = $value['state'];
                        $errorsData[$recordCount]['Zip']	           	= $value['zipCode'];
                        $errorsData[$recordCount]['Error Description']  = $errorFieldMessage;    
                    }
                    $count++;
                }
                
                $response['data']['message'] = 'Invalid input values.';
                $response['status'] = true;
                $response['data']['result']['total_records']    = $totalData;
                $response['data']['result']['total_saved']      = $savedCount;
                $response['data']['result']['total_updated']    = $updateCount;
                $response['data']['result']['successCount']     = $savedCount;
                $response['data']['result']['errorCount']       = $errorCount;
                    
                if(!empty($errorsData))
                {
                    $data = collect($errorsData);
                    $model = new InvoicesExport($data,"LocalResources");
                    $filename = $user->id.'_localResourceErrors_'.date('d_M_Y_H_i_s').'.xlsx';
                    ($model)->store('files/'.$filename);
                    $response['data']['errorFile'] = $model->getFileUrl($filename);
                    $response['data']['result']['total_unsaved'] = $errorCount;
                    $response['data']['code'] = 500;
                    return $response;
                }
                $response['data']['code'] = 200;
                $response['data']['message'] = 'Local Resource imported successfully';
            }
        }
        return $response;
    }


    public function crisisResource(Request $request)
    {
        $request['jsonData'] = base64_decode($request->jsonData);
        $request['schoolProfileId'] = base64_decode($request->schoolProfileId);
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'    => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified())
        { 
            $rules = [
                'jsonData'          => ['required','json'],
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) 
            {
                $response['data']['message'] = 'Invalid input values.';
                $response['data']['errors'] = $validator->messages();
            }
            else
            {
                $jsonData = json_decode($request->get('jsonData'));
                $jsonData = $jsonData->CrisisResource;
                
                $schoolProfileId = $request->schoolProfileId;
                
                $errorsData = [];

                $errorFieldMessage = "";
                $errorFieldStatus = false;
                
                $totalData=count($jsonData);
                
                $recordCount    = 0;
                $count          = 1;
                $savedCount     = 0;
                $errorCount     = 0;
                $updateCount    = 0;

               
                foreach ($jsonData as $attributes) 
                {
                    $errorFieldMessage = "";
                    $errorFieldStatus = false;
                    $value = (Array) $attributes;
                    // if($count==2)
                    // {
                    //     break;
                    // }

                    // ================================ Required Checks =====================================//
                    if(!isset($value['name']) || $value['name']==null)
                    {
                        $value['name'] = null;
                        $errorFieldStatus   = true;
                        $errorFieldMessage  = $errorFieldMessage."The name of crisis resource is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['name']);
                    	$lengthLimit		= 50;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                        	$errorFieldMessage  = $errorFieldMessage."Crisis resource name is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['phoneNumber']) || $value['phoneNumber']==null)
                    {
                        $value['phoneNumber'] = null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Phone number is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Phone number is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['phoneNumber']);
                    	$lengthLimit		= 15;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Phone number is limited to ".$lengthLimit." digits";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Phone number is limited to ".$lengthLimit." digits";
                    	}

                    	if(!is_numeric($value['phoneNumber']))
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Please enter a valid phone number";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Please enter a valid phone number";
                    	}
                    }

                    if(!isset($value['serviceTypeId']) || $value['serviceTypeId']==null)
                    {
                        $value['serviceTypeId'] = null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Description of service(s) is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Description of service(s) is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['serviceTypeId']);
                    	$lengthLimit		= 1000;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Description Of service(s) is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Description Of service(s) is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['website']) || $value['website']==null)
                    {
                        $value['website'] = null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Website is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Website is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['website']);
                    	$lengthLimit		= 200;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Website domain is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Website domain is limited to ".$lengthLimit." characters";
                    	}

                    	if(!filter_var($value['website'], FILTER_VALIDATE_URL))
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Please enter a valid website";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Please enter a valid website";
                    	}
                    	
                    }

                    if(!isset($value['type']) || $value['type']==null)
                    {
                        $value['type'] = null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Please indicate if it is a Hotline or Textline";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Please indicate if it is a Hotline or Textline";
                    }
                    else
                    {
                    	if($value['type']=="Hotline" || $value['type']=="Textline")
                    	{
                    	}
                    	else
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Invalid. Please indicate if it is a Hotline or Textline";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Invalid. Please indicate if it is a Hotline or Textline";
                    	}
                    }

                    // =========================== Required Checks Ends ================================//

                    if($errorFieldStatus==false)
                    {
                        $crisisResource = CrisisResource::where('schoolProfileId','=',$schoolProfileId)
                                                        ->where('name','=',$value['name'])
                                                        ->count();
                        if ($crisisResource>0) 
                        {
                            $crisisResource = CrisisResource::where('name','=',$value['name'])
                                                            ->where('schoolProfileId','=',$schoolProfileId)
                                                            ->update($value);
                            $updateCount++;
                        }
                        else
                        {
                            $crisisResource = CrisisResource::create([
                                                                        "name" => $value['name'],
                                                                        "serviceTypeId" => $value['serviceTypeId'],
                                                                        "phoneNumber" => $value['phoneNumber'],
                                                                        "website" => $value['website'],
                                                                        "type" => $value['type'],
                                                                        "schoolProfileId" => $schoolProfileId,
                                                                        
                                                                    ]);
                            $savedCount++;
                        }
                    }
                    else
                    {
                        $recordCount++;
                        $errorCount++;
                        $errorsData[$recordCount]['Crisis Resource Name']      	= $value['name'];
                        $errorsData[$recordCount]['Description Of Service(s)'] 	= $value['serviceTypeId'];
                        $errorsData[$recordCount]['Phone Number']       		= $value['phoneNumber'];
                        $errorsData[$recordCount]['Type(Hotline/Textline)']     = $value['type'];
                        $errorsData[$recordCount]['Website']            = $value['website'];  
                        $errorsData[$recordCount]['Error Description']  = $errorFieldMessage;    
                    }
                    $count++;
                }
                
                $response['data']['message'] = 'Invalid input values.';
                $response['status'] = true;
                $response['data']['result']['total_records']    = $totalData;
                $response['data']['result']['total_saved']      = $savedCount;
                $response['data']['result']['total_updated']    = $updateCount;
                $response['data']['result']['successCount']     = $savedCount;
                $response['data']['result']['errorCount']       = $errorCount;
                    
                if(!empty($errorsData))
                {
                    $data = collect($errorsData);
                    $model = new InvoicesExport($data,"CrisisResources");
                    $filename = $user->id.'_crisisResourceErrors_'.date('d_M_Y_H_i_s').'.xlsx';
                    ($model)->store('files/'.$filename);
                    $response['data']['errorFile'] = $model->getFileUrl($filename);
                    $response['data']['result']['total_unsaved'] = $errorCount;
                    $response['data']['code'] = 500;
                    return $response;
                }
                $response['data']['code'] = 200;
                $response['data']['message'] = 'Crisis Resource imported successfully';
            }
        }
        return $response;
    }

    public function responder(Request $request)
    {
        $request['jsonData'] = base64_decode($request->jsonData);
        $request['schoolProfileId'] = base64_decode($request->schoolProfileId);
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'    => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified())
        { 
            $rules = [
                'jsonData'          => ['required','json'],
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) 
            {
                $response['data']['message']    = 'Invalid input values.';
                $response['data']['errors']     = $validator->messages();
            }
            else
            {
                $jsonData = json_decode($request->get('jsonData'));
                $jsonData = $jsonData->Responder;

                $schoolProfileId = $request->schoolProfileId;
                
                $errorsData = [];

                $errorFieldMessage = "";
                $errorFieldStatus = false;
                
                $totalData=count($jsonData);
                
                $recordCount    = 0;
                $count          = 1;
                $savedCount     = 0;
                $errorCount     = 0;
                $updateCount    = 0;

               
                foreach ($jsonData as $attributes) 
                {
                    $errorFieldStatus = false;
                    $errorFieldMessage = "";
                    $value = (Array) $attributes;
                    // if($count==$totalData)
                    // {
                    //     break;
                    // }

                    // ================================ Required Checks =====================================//
                    if(!isset($value['responderId']) || $value['responderId']==null)
                    {
                        $value['responderId']=null;
                        $errorFieldStatus   = true;
                        $errorFieldMessage  = $errorFieldMessage."Employee ID is required";
                    }
                    if(!isset($value['firstName']) || $value['firstName']==null)
                    {
                        $value['firstName']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Responder first name is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Responder first name is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['firstName']);
                    	$lengthLimit		= 20;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Responder first name is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Responder first name is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['lastName']) || $value['lastName']==null)
                    {
                        $value['lastName']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Responder last name is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Responder last name is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['lastName']);
                    	$lengthLimit		= 20;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Responder last name is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Responder last name is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['title']) || $value['title']==null)
                    {
                        $value['title']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Title is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Title is required";
                    }
                    else
                    {
                    	if($value['title']=="Mr." || $value['title']=="Mrs." 
                            || $value['title']=="Miss" || $value['title']=="Ms." 
                            || $value['title']=="Prof."
                            || $value['title']=="Dr.")
                    	{

                    	}
                    	else
                    	{
                    		$errorFieldStatus   = true;
	                        if($errorFieldMessage!="")
	                            $errorFieldMessage  = $errorFieldMessage.", Invalid. Title should be one of following (Mr., Mrs., Miss, Ms., Prof. or Dr.)";
	                        else
	                            $errorFieldMessage  = $errorFieldMessage."Invalid. Title should be one of following (Mr., Mrs., Miss, Ms., Prof. or Dr.)";
                    	}
                    }

                    if(!isset($value['username']) || $value['username']==null)
                    {
                        $value['username']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Primary email is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Primary email is required";
                    }
                    else
                    {
                    	if (!filter_var($value['username'], FILTER_VALIDATE_EMAIL)) 
                    	{
                    	    if($errorFieldMessage!="")
                    	        $errorFieldMessage  = $errorFieldMessage.", Please enter a valid email";
                    	    else
                    	        $errorFieldMessage  = $errorFieldMessage."Please enter a valid email";
                    	}
                    }
                    if(!isset($value['position']) || $value['position']==null)
                    {
                        $value['position']=null;
                        $errorFieldStatus   = true;
                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Position/Role is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Position/Role is required";
                    }
                    else
                    {
                        $responderCategory = ResponderCategory::where('schoolProfileId','=',$schoolProfileId)
                                                                ->where('positionName','=',$value['position'])
                                                                ->count();
                        if ($responderCategory==0) 
                        {
                            $errorFieldStatus   = true;
                            if($errorFieldMessage!="")
                                $errorFieldMessage  = $errorFieldMessage.", Please enter a valid position/role level that aligns with the responder level (1,2, or 3). Refer to Responder Segmentation under Account Settings.";
                            else
                                $errorFieldMessage  = $errorFieldMessage."Please enter a valid position/role level that aligns with the responder level (1,2, or 3). Refer to Responder Segmentation under Account Settings.";
                        }
                    }

                    if(!isset($value['level']) || $value['level']==null)
                    {
                        $value['level']=null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Responder level (1,2, or 3) is required.";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Responder level (1,2, or 3) is required.";
                    }
                    else
                    {
                        if($value['level']=="1" || $value['level']=="2" || $value['level']=="3")
                        {
                        }
                        else
                        {
                            $errorFieldStatus   = true;
                            if($errorFieldMessage!="")
                                $errorFieldMessage  = $errorFieldMessage.", Please enter a valid responder level that aligns with the position/role. Refer to Responder Segmentation under Account Settings.";
                            else
                                $errorFieldMessage  = $errorFieldMessage."Please enter a valid responder level that aligns with the position/role. Refer to Responder Segmentation under Account Settings.";
                        } 
                    }
                    // =========================== Required Checks Ends ================================//
                    
                    if($errorFieldStatus==false)
                    {
                        $emailAvailable = User::where('username','=',$value['username'])->first();
                        if(!empty($emailAvailable))
                        {
                            
                            if($emailAvailable->isResponder())
                            {
                                
                                if($emailAvailable->Responder->responderId == $value['responderId'])
                                {
                                    $responderCategory = ResponderCategory::where('schoolProfileId','=',$schoolProfileId)
                                                                    ->where('positionName','=',$value['position'])
                                                                    ->first();
                                    $responder = Responder::where('responderId','=',$value['responderId'])
                                                            ->update([
                                                                'title'                 => $value['title'],
                                                                'firstName'             => $value['firstName'],
                                                                'lastName'              => $value['lastName'],
                                                                'position'              => $responderCategory->id,
                                                            ]);
                                    $updateCount++;
                                }
                                else
                                {
                                    $responderIdAvailable = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                                            ->where('responderId','=',$value['responderId'])
                                                            ->where('isDeleted','=',0)
                                                            ->first();                          
                                    if(!empty($responderIdAvailable))
                                    {
                                        $errorFieldStatus   = true;
                                        if($errorFieldMessage!="")
                                            $errorFieldMessage  = $errorFieldMessage.", The employee ID conflicts with another user";
                                        else
                                            $errorFieldMessage  = $errorFieldMessage."The employee ID conflicts with another user";
                                    }
                                    else
                                    {
                                        $errorFieldStatus   = true;
                                        if($errorFieldMessage!="")
                                            $errorFieldMessage  = $errorFieldMessage.", You cannot update Responders' employee ID";
                                        else
                                            $errorFieldMessage  = $errorFieldMessage."You cannot update Responders' employee ID";
                                    }
                                }
                            }
                            else
                            {
                                $errorFieldStatus   = true;
                                if($errorFieldMessage!="")
                                    $errorFieldMessage  = $errorFieldMessage.", Email conflict with another user";
                                else
                                    $errorFieldMessage  = $errorFieldMessage."Email conflict with another user";
                            }
                        }
                        else
                        {
                            $responderIdAvailable = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                                            ->where('responderId','=',$value['responderId'])
                                                            ->where('isDeleted','=',0)
                                                            ->first();                          
                            if(!empty($responderIdAvailable))
                            {
                                $errorFieldStatus   = true;
                                if($errorFieldMessage!="")
                                    $errorFieldMessage  = $errorFieldMessage.", You cannot update Responders' email";
                                else
                                    $errorFieldMessage  = $errorFieldMessage."You cannot update Responders' email";
                            }
                            else
                            { 
                                $rolesId = Roles::findByAttr('label',User::USER_RESPONDER)->id;

                                $authorizationCode = rand(100000,999999);
                                // First Enter Data in users Table
                                $modelUser = User::create([
                                    'username'  => $value['username'],
                                    'password'  => bcrypt($authorizationCode),
                                    'roleId'    => $rolesId,
                                    'verified'  => User::STATUS_INACTIVE
                                ]);
                                if($modelUser)
                                {
                                    $modelSchoolProfile = SchoolProfiles::find($schoolProfileId);
                                    if(!empty($modelSchoolProfile))
                                    {
                                       
                                        $responderCategory = ResponderCategory::where('schoolProfileId','=',$schoolProfileId)
                                                                ->where('positionName','=',$value['position'])
                                                                ->first();
                                        $responder = Responder::create([
                                            'title'                 => $value['title'],
                                            'firstName'             => $value['firstName'],
                                            'lastName'              => $value['lastName'],
                                            'responderId'           => $value['responderId'],
                                            'userId'                => $modelUser->id,
                                            'position'              => $responderCategory->id,
                                            'schoolProfileId'       => $modelSchoolProfile->id,
                                            'authorizationCode'     => $authorizationCode,
                                            'isAvailable'           => 0,
                                        ]);                                        
                                        if ($responder) 
                                        {
                                            $savedCount++;
                                            $responder->sendEmail();
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if($errorFieldMessage!="")
                    {
                        $recordCount++;
                        $errorCount++;
                        $errorsData[$recordCount]['Title']                      = $value['title'];
                        $errorsData[$recordCount]['Responder First Name']       = $value['firstName'];
                        $errorsData[$recordCount]['Responder Last Name']        = $value['lastName'];
                        $errorsData[$recordCount]['Primary Email']              = $value['username'];
                        $errorsData[$recordCount]['Employee ID']                = $value['responderId'];
                        $errorsData[$recordCount]['Position/Role']              = $value['position'];
                        $errorsData[$recordCount]['Responder Level (1/2/3)']    = $value['level'];
                        $errorsData[$recordCount]['Error Description']          = $errorFieldMessage;    
                    }
                    $count++;
                }
                
                $response['data']['message'] = 'Invalid input values.';
                $response['status'] = true;
                $response['data']['result']['total_records']    = $totalData;
                $response['data']['result']['total_saved']      = $savedCount;
                $response['data']['result']['total_updated']    = $updateCount;
                $response['data']['result']['successCount']     = $savedCount;
                $response['data']['result']['errorCount']       = $errorCount;
                    
                if(!empty($errorsData))
                {
                    $data = collect($errorsData);
                    $model = new InvoicesExport($data,"Responders");
                    $filename = $user->id.'_responderErrors_'.date('d_M_Y_H_i_s').'.xlsx';
                    ($model)->store('files/'.$filename);
                    $response['data']['errorFile'] = $model->getFileUrl($filename);
                    $response['data']['result']['total_unsaved'] = $errorCount;
                    $response['data']['code'] = 500;
                    return $response;
                }
                $response['data']['code'] = 200;
                $response['data']['message'] = 'Responders imported successfully';
            }
        }
        return $response;
    }

    public function student(Request $request)
    {
        $request['jsonData'] = base64_decode($request->jsonData);
        $request['schoolProfileId'] = base64_decode($request->schoolProfileId);
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'    => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && $user->statusVerified())
        { 
            $rules = [
                'jsonData'          => ['required','json'],
                'schoolProfileId'   => ['required','exists:school_profiles,id'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) 
            {
                $response['data']['message']    = 'Invalid input values.';
                $response['data']['errors']     = $validator->messages();
            }
            else
            {
                $jsonData = json_decode($request->get('jsonData'));
                $jsonData = $jsonData->Student;

                $schoolProfileId = $request->schoolProfileId;
                
                $errorsData = [];

                $errorFieldMessage = "";
                $errorFieldStatus = false;
                
                $totalData=count($jsonData);
                
                $recordCount    = 0;
                $count          = 1;
                $savedCount     = 0;
                $errorCount     = 0;
                $updateCount    = 0;

               
                foreach ($jsonData as $attributes) 
                {
                    $errorFieldMessage = "";
                    $errorFieldStatus = false;
                    $value = (Array) $attributes;
                    // if($count==$totalData)
                    // {
                    //     break;
                    // }

                    // ================================ Required Checks =====================================//
                    if(!isset($value['responderId']) || $value['responderId']==null)
                    {
                        $value['responderId']=null;
                        $errorFieldStatus   = true;
                        $errorFieldMessage  = $errorFieldMessage."The employee ID of this student's primary Responder is required";
                    }
                    else
                    {
                        $responderProfile = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                                            ->where('responderId','=',$value['responderId'])
                                                            ->where('isDeleted','=',0)
                                                            ->first();
                        if(empty($responderProfile))
                        {  
                            if($errorFieldMessage!="")
                                $errorFieldMessage  = $errorFieldMessage.", Inavlid Responder Id";
                            else
                                $errorFieldMessage  = $errorFieldMessage."Inavlid Responder Id";
                        }
                    }

                    if(!isset($value['studentId']) || $value['studentId']==null)
                    {
                        $value['studentId']=null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Student ID is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Student ID is required";
                    }

                    if(!isset($value['firstName']) || $value['firstName']==null)
                    {
                        $value['firstName']=null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Student first name is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Student first name is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['firstName']);
                    	$lengthLimit		= 20;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Student first name is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Student first name is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['lastName']) || $value['lastName']==null)
                    {
                        $value['lastName']=null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Student last name is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Student last name is required";
                    }
                    else
                    {
                    	$lengthChecker 		= strlen($value['lastName']);
                    	$lengthLimit		= 20;
                    	if($lengthChecker>$lengthLimit)
                    	{
                    		$errorFieldStatus   = true;
                    		if($errorFieldMessage!="")
                    		    $errorFieldMessage  = $errorFieldMessage.", Student last name is limited to ".$lengthLimit." characters";
                    		else
                    		    $errorFieldMessage  = $errorFieldMessage."Student last name is limited to ".$lengthLimit." characters";
                    	}
                    }

                    if(!isset($value['username']) || $value['username']==null)
                    {
                        $value['username']=null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Email is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Email is required";
                    }
                    else
                    {
                    	if (!filter_var($value['username'], FILTER_VALIDATE_EMAIL)) 
                    	{
                    	    if($errorFieldMessage!="")
                    	        $errorFieldMessage  = $errorFieldMessage.", Please enter a valid email";
                    	    else
                    	        $errorFieldMessage  = $errorFieldMessage."Please enter a valid email";
                    	}
                    }

                    if(!isset($value['gradeLevel']) || $value['gradeLevel']==null)
                    {
                        $value['gradeLevel']=null;
                        $errorFieldStatus   = true;

                        if($errorFieldMessage!="")
                            $errorFieldMessage  = $errorFieldMessage.", Grade/year level is required";
                        else
                            $errorFieldMessage  = $errorFieldMessage."Grade/year level is required";
                    }
                    // =========================== Required Checks Ends ================================//
                    
                    if($errorFieldStatus==false)
                    {
                        $emailAvailable = User::where('username','=',$value['username'])->first();
                        if(!empty($emailAvailable))
                        {
                            if($emailAvailable->isStudent())
                            {
                                if($emailAvailable->student->studentId == $value['studentId'])
                                {
                                    $responderProfile = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                                            ->where('responderId','=',$value['responderId'])
                                                            ->where('isDeleted','=',0)
                                                            ->first();
                                    if(!empty($responderProfile))
                                    {
                                        $studentResponder = StudentResponder::
                                                                where('studentProfileId',$emailAvailable->student->studentId)
                                                                ->where('responderProfileId',$responderProfile->id)
                                                                ->first();
                                        if(empty($studentResponder))
                                        {
                                            $student = Student::where('studentId','=',$value['studentId'])
                                                                    ->update([
                                                                        'firstName'             => $value['firstName'],
                                                                        'lastName'              => $value['lastName'],
                                                                        'gradeLevel'            => $value['gradeLevel'],
                                                                    ]);
                                            $updateCount++;
                                        }
                                        else
                                        {
                                            $studentResponder = StudentResponder::create([
                                                                                    'studentProfileId' => $emailAvailable->student->studentId,
                                                                                    'responderProfileId'=> $responderProfile->id,
                                                                                    'verified'  => 1,
                                                                                ]);

                                            $student = Student::where('studentId','=',$value['studentId'])
                                                                    ->update([
                                                                        'firstName'             => $value['firstName'],
                                                                        'lastName'              => $value['lastName'],
                                                                        'gradeLevel'            => $value['gradeLevel'],
                                                                    ]);
                                            if($studentResponder && $student)
                                            {
                                                $updateCount++;
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $errorFieldStatus   = true;

                                        if($errorFieldMessage!="")
                                            $errorFieldMessage  = $errorFieldMessage.", The employee ID of this student's primary Responder is invalid";
                                        else
                                            $errorFieldMessage  = $errorFieldMessage."The employee ID of this student's primary Responder is invalid";
                                    }
                                }
                                else
                                {
                                    $errorFieldStatus   = true;
                                    if($errorFieldMessage!="")
                                        $errorFieldMessage  = $errorFieldMessage.", The student ID conflicts with another user";
                                    else
                                        $errorFieldMessage  = $errorFieldMessage."The student ID conflicts with another user";
                                }
                            }
                            else
                            {
                            	$errorFieldStatus   = true;
                            	if($errorFieldMessage!="")
                            	    $errorFieldMessage  = $errorFieldMessage.", Student email conflict with some another user";
                            	else
                            	    $errorFieldMessage  = $errorFieldMessage."Student email conflict with some another user";
                            }
                        }
                        else
                        {
                            $studentIdAvailable = Student::join('users', 'users.id', '=', 'student_profiles.userId')
                                                            ->where('studentId','=',$value['studentId'])
                                                            ->where('isDeleted','=',0)
                                                            ->first();

                            if(!empty($studentIdAvailable))
                            {
                                $errorFieldStatus   = true;
                                if($errorFieldMessage!="")
                                    $errorFieldMessage  = $errorFieldMessage.", You cannot update the student's email";
                                else
                                    $errorFieldMessage  = $errorFieldMessage."You cannot update the student's email";
                            }
                            else
                            { 
                                $responderProfile = Responder::where('responderId','=',$value['responderId'])->first();
                                if(!empty($responderProfile))
                                {
                                	if($responderProfile->getCategory->levelId == 1)
                                	{
	                                    $rolesId = Roles::findByAttr('label','student')->id;
	                                    $authorizationCode = rand(100000,999999);
	                                    // Second Enter Data in users Table
	                                    $user = User::create([
	                                        'username'  => $value['username'],
	                                        'password'  => bcrypt($authorizationCode),
	                                        'roleId'    => $rolesId,
	                                        'verified'  => User::STATUS_INACTIVE
	                                    ]);
	                                    $studentProfile = Student::create([
	                                        'userId'            => $user->id,
	                                        'schoolProfileId'   => $schoolProfileId,
	                                        'studentId'         => $value['studentId'],
	                                        'firstName'         => $value['firstName'],
	                                        'lastName'          => $value['lastName'],
	                                        'gradeLevel'        => $value['gradeLevel'],
	                                        'authorizationCode' => $authorizationCode,
	                                    ]);
	                                    $studentResponder = StudentResponder::create([
	                                        'studentProfileId'      => $studentProfile->id,
	                                        'responderProfileId'    => $responderProfile->id,
	                                        'verified'              => 1,
	                                    ]);
	                                    
	                                    if ($user && $studentProfile && $studentResponder) 
	                                    {
	                                        $savedCount++;
	                                        $studentProfile->sendEmail();
	                                    }
	                                }
	                                else
	                                {
	                                	$errorFieldStatus   = true;
	                                    if($errorFieldMessage!="")
	                                        $errorFieldMessage  = $errorFieldMessage.", The employee ID of this student's primary Responder is invalid.";
	                                    else
	                                        $errorFieldMessage  = $errorFieldMessage."The employee ID of this student's primary Responder is invalid.";	
	                                }
                                }
                                else
                                {
                                    $errorFieldStatus   = true;

                                    if($errorFieldMessage!="")
                                        $errorFieldMessage  = $errorFieldMessage.", The employee ID of this student's primary Responder is invalid.";
                                    else
                                        $errorFieldMessage  = $errorFieldMessage."The employee ID of this student's primary Responder is invalid.";
                                }
                            }
                        }
                    }
                    if($errorFieldMessage!="")
                    {
                        $recordCount++;
                        $errorCount++;
                        $errorsData[$recordCount]['Student First Name']                 = $value['firstName'];
                        $errorsData[$recordCount]['Student Last Name']                  = $value['lastName'];
                        $errorsData[$recordCount]['Grade/Year Level']                   = $value['gradeLevel'];
                        $errorsData[$recordCount]['Email']                              = $value['username'];
                        $errorsData[$recordCount]['Student ID']                         = $value['studentId'];
                        $errorsData[$recordCount]['Designated Responder ID']            = $value['responderId'];
                        $errorsData[$recordCount]['Error Description']                  = $errorFieldMessage;    
                    }
                    $count++;
                }
                
                $response['data']['message'] = 'Invalid input values.';
                $response['status'] = true;
                $response['data']['result']['total_records']    = $totalData;
                $response['data']['result']['total_saved']      = $savedCount;
                $response['data']['result']['total_updated']    = $updateCount;
                $response['data']['result']['successCount']     = $savedCount;
                $response['data']['result']['errorCount']       = $errorCount;
                    
                if(!empty($errorsData))
                {
                    $data = collect($errorsData);
                    $model = new InvoicesExport($data,"Students");
                    $filename = $user->id.'_studentErrors_'.date('d_M_Y_H_i_s').'.xlsx';
                    ($model)->store('files/'.$filename);
                    $response['data']['errorFile'] = $model->getFileUrl($filename);
                    $response['data']['result']['total_unsaved'] = $errorCount;
                    $response['data']['code'] = 500;
                    return $response;
                }
                $response['data']['code'] = 200;
                $response['data']['message'] = 'Responders imported successfully';
            }
        }
        return $response;
    }
}
