<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/// JWT ///
use JWTAuthException;
use JWTAuth;

/// Models ////
use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiSchoolAdminProfiles as SchoolAdminProfiles;
use App\Models\Api\ApiSchoolProfiles as SchoolProfiles;
use App\Models\Api\ApiStudentResponder as StudentResponder;
use App\Models\Api\ApiStudent as Student;
use App\Models\Api\ApiResponder as Responder;
use App\Models\Api\ApiLocalResource as LocalResource;
use App\Models\Api\ApiCrisisResource as CrisisResource;
use App\Models\DataExport;
use PDF;

class ExportDataController extends Controller
{
    // Export Function for Local Resources
    public function LocalResource(Request $request)
    {

        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && ($user->isAdmin()||$user->isSecondaryAdmin()) && $user->statusVerified())
        { 
            $response['data']['code'] = 500;
            $response['data']['message'] = 'No Record Found!';
            $response['status'] = false;

            // Getting Name for Admin or Secondary Admin
            if($user->isAdmin())
            {
                $userName = $user->schoolAdminProfile->firstName." ".$user->schoolAdminProfile->lastName;
                $schoolProfileId = $user->schoolAdminProfile->schoolProfile->id;
            }
            else
            {
                $userName = $user->schoolSecondaryAdminProfile->firstName." ".$user->schoolSecondaryAdminProfile->lastName;
                $schoolProfileId = $user->schoolSecondaryAdminProfile->schoolProfile->id;
            }
            
            // Find local resource for specific school
            $localResource=LocalResource::where('schoolProfileId','=',$schoolProfileId)->get();
            
            // Find School To get time zone.
            $schoolProfile = SchoolProfiles::find($schoolProfileId);
            // Generating date in specific format according to the school time zone.
            $reportDate =  date('jS F Y g:i A',strtotime($schoolProfile->schoolTimeZone." Hours"));

            
            // Check the result of localresource is empty or not.
            if(!empty($localResource)){
                // Loading pdf view in variable.
                $pdf = PDF::loadView('localResource', ['localResource'=>$localResource,'schoolProfile'=>$schoolProfile,'reportDate'=>$reportDate,'userName'=>$userName]);
				
                $myDate = 'LocalResource_'.date('d_M_Y_H_i_s').'.pdf';
                $path = storage_path().'/app/files/'.$myDate;
				$pdf->save($path);
				
                $response['data']['code'] = 200;
                $response['data']['message'] = 'Request Successfull';
                $response['data']['result'] = base64_encode("https://www.thewavesapp.online/wavesbackend/storage/app/files/".$myDate);
                $response['status'] = true;
                // Download the pdf file.
                // return $pdf->download('LocalResource.pdf');
            }
        }
        return $response;
    }

    // Export Function for Crisi Support
    public function crisisSupport(Request $request)
    {
        // Validating user with the help of token.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        
        if(!empty($user) && ($user->isAdmin()||$user->isSecondaryAdmin()) && $user->statusVerified())
        { 
            $response['data']['code'] = 500;
            $response['data']['message'] = 'No Record Found!';
            $response['status'] = true;
            
            // Getting Admin or Secondary Admin name.
            if($user->isAdmin())
            {
                $userName = $user->schoolAdminProfile->firstName." ".$user->schoolAdminProfile->lastName;
                $schoolProfileId = $user->schoolAdminProfile->schoolProfile->id;
            }
            else
            {
                $userName = $user->schoolSecondaryAdminProfile->firstName." ".$user->schoolSecondaryAdminProfile->lastName;
                $schoolProfileId = $user->schoolSecondaryAdminProfile->schoolProfile->id;
            }

            // Find Crisi Supports for Specific School
            $crisisSupport=CrisisResource::where('schoolProfileId','=',$schoolProfileId)->get();

            // Finding school to get timezone.
            $schoolProfile = SchoolProfiles::find($schoolProfileId);
            
            // Generate time in a specific format according to school timezone.
            $reportDate =  date('jS F Y g:i A',strtotime($schoolProfile->schoolTimeZone." Hours"));

            // Check Crisis support result is empty or not.
            if(!empty($crisisSupport)){
                // Load pdf file in a variable.
                $pdf = PDF::loadView('crisisSupport', ['crisisSupport'=>$crisisSupport,'schoolProfile'=>$schoolProfile,'reportDate'=>$reportDate,'userName'=>$userName]);
                
                $myDate = 'CrisisSupportResources_'.date('d_M_Y_H_i_s').'.pdf';
                $path = storage_path().'/app/files/'.$myDate;
                $pdf->save($path);

                $response['data']['code'] = 200;
                $response['data']['message'] = 'Request Successfull';
                $response['data']['result'] = base64_encode("https://www.thewavesapp.online/wavesbackend/storage/app/files/".$myDate);
                $response['status'] = true;
                // Download the pdf file.
                //return $pdf->download('CrisisSupport.pdf');
            }
        }
        
        return $response;
    }

    // Export Function for Students
    public function student(Request $request)
    {
        // Validating user with the help of token.
        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && ($user->isAdmin()||$user->isSecondaryAdmin()) && $user->statusVerified()) 
        { 
            $response['data']['code'] = 500;
            $response['data']['message'] = 'No Record Found!';
            $response['status'] = true;

            // Getting the name for Admin or SecondaryAdmin.
            if($user->isAdmin())
            {
                $userName = $user->schoolAdminProfile->firstName." ".$user->schoolAdminProfile->lastName;
                $schoolProfileId = $user->schoolAdminProfile->schoolProfile->id;
            }
            else
            {
                $userName = $user->schoolSecondaryAdminProfile->firstName." ".$user->schoolSecondaryAdminProfile->lastName;
                $schoolProfileId = $user->schoolSecondaryAdminProfile->schoolProfile->id;
            }

            // Finding Students belongs to a particular school.
            $student =  DB::table('student_profiles')
                            ->join('users', 'users.id', '=', 'student_profiles.userId')
                            ->select('student_profiles.*','users.*','student_profiles.id as studentProfileId')
                            ->where('student_profiles.schoolProfileId','=',$schoolProfileId)
                            ->where('isDeleted','=',0)
                            ->get();

            foreach($student as $std)
            {
                $std->fullName= $std->firstName." ".$std->lastName;

                // Finding Sutdent relation with its primary responder.
                $student_responder = StudentResponder::where('studentProfileId','=',$std->studentProfileId)
                ->first();
                
                // After finding relation get specific responder data.
                $responder = Responder::find($student_responder->responderProfileId);

                // Add responder to Specific student response.
                $std->responderAttached= $responder;
            }

            // Find school to get school time zone.
            $schoolProfile = SchoolProfiles::find($schoolProfileId);

            // Generate datetime in a specific format according to school time zone.
            $reportDate =  date('jS F Y g:i A',strtotime($schoolProfile->schoolTimeZone." Hours"));

            //  Check if the result is success or  not.
            if(!empty($student)){
                // Loading pdf view in a variable.
                $pdf = PDF::loadView('student', ['student'=>$student,'schoolProfile'=>$schoolProfile,'reportDate'=>$reportDate,'userName'=>$userName]);
                
                $myDate = 'Student_'.date('d_M_Y_H_i_s').'.pdf';
                $path = storage_path().'/app/files/'.$myDate;
                $pdf->save($path);

                $response['data']['code'] = 200;
                $response['data']['message'] = 'Request Successfull';
                $response['data']['result'] = base64_encode("https://www.thewavesapp.online/wavesbackend/storage/app/files/".$myDate);
                $response['status'] = true;
                // Download the generated pdf file.
                //return $pdf->download('Students.pdf');
            }
        }
        return $response;
    }

    public function responder(Request $request)
    {

        $user = JWTAuth::toUser($request->token);
        $response = [
                'data' => [
                    'code'      => 400,
                    'errors'     => '',
                    'message'   => 'Invalid Token! User Not Found.',
                ],
                'status' => false
            ];
        if(!empty($user) && ($user->isAdmin()||$user->isSecondaryAdmin()) && $user->statusVerified())
        { 
            $response['data']['code'] = 500;
            $response['data']['message'] = 'No Record Found!';
            $response['status'] = true;
            
            // Getting the name for Admin or Secondary Admin.
            if($user->isAdmin())
            {
                $userName = $user->schoolAdminProfile->firstName." ".$user->schoolAdminProfile->lastName;
                $schoolProfileId = $user->schoolAdminProfile->schoolProfile->id;
            }
            else
            {
                $userName = $user->schoolSecondaryAdminProfile->firstName." ".$user->schoolSecondaryAdminProfile->lastName;
                $schoolProfileId = $user->schoolSecondaryAdminProfile->schoolProfile->id;
            }

            // Getting Responder Data.
            $responder = Responder::join('users', 'users.id', '=', 'responder_profiles.userId')
                                    ->select('responder_profiles.id','responder_profiles.userId','users.username','users.avatarFilePath','responder_profiles.firstName','responder_profiles.lastName','responder_profiles.title','responder_profiles.responderId','responder_profiles.authorizationCode','users.verified','responder_profiles.position')
                                    ->where('schoolProfileId','=',$schoolProfileId)
                                    ->where('isDeleted','=',0)
                                    ->get();

            // Getting Positions Name and make full names.
            foreach($responder as $res)
            {
                $res['resNameId']= $res->firstName."(".$res->responderId.")";
                $res['fullName']= $res->firstName." ".$res->lastName;
                $res['position']= $res->getCategory->positionName;
            }

            // Finding School To get School Time Zone.
            $schoolProfile = SchoolProfiles::find($schoolProfileId);

            // Generate datetime according to school time zone.
            $reportDate =  date('jS F Y g:i A',strtotime($schoolProfile->schoolTimeZone." Hours"));
            
            if ($responder) {
                // Generating the pdf view in a variabele.
                $pdf = PDF::loadView('responder', ['responder'=>$responder,'schoolProfile'=>$schoolProfile,'reportDate'=>$reportDate,'userName'=>$userName]);


                $myDate = 'Responder_'.date('d_M_Y_H_i_s').'.pdf';
                $path = storage_path().'/app/files/'.$myDate;
                $pdf->save($path);

                $response['data']['code'] = 200;
                $response['data']['message'] = 'Request Successfull';
                $response['data']['result'] = base64_encode("https://www.thewavesapp.online/wavesbackend/storage/app/files/".$myDate);
                $response['status'] = true;
                // Downloading the pdf file.
                //return $pdf->download('Responders.pdf');
            }
        }
        return $response;
    }
}
