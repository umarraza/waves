<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Models\Api\ApiUser as User;
use App\Models\Api\ApiStudent as Student;
use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function createStudents(Request $request)
    {
        $response = [
            'data' => [
                'error' => 400,
               	'message' => 'Something went wrong. Please try again later!',
            ],
           'status' => false
        ];
        $rules = [
           'username'   => ['required', 'email', 'max:191', Rule::unique('users')],
           'designatedResponder'   => 'required',
           'firstName' => 'required',
           'lastName' => 'required',
           'gradeLevel' => 'required',
           'schoolId' => 'required',
           'studentId' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response['data']['message'] = 'Invalid input values.';
            $response['data']['errors'] = $validator->messages();
        }else
        {
            $rolesId = Roles::findByAttr('label',Roles::STUDENT)->id;

            // First Enter Data in users Table
            $user = User::create([
                'username'  => $request->get('username'),
                'password'  => bcrypt('defaultPassword'),
                'roleId'    => $rolesId,
                'verified'  => User::STATUS_INACTIVE
            ]);
            // Second Enter Data in studentProfiles Table
            $studentProfile = Student::create([
                'userId'  			=> $user->id,
                'schoolProfileId'  	=> $request->get('schoolId'),
                'studentId'    		=> $request->get('studentId'),
                'firstName'    		=> $request->get('firstName'),
                'lastName'    		=> $request->get('lastName'),
                'gradeLevel'    	=> $request->get('gradeLevel'),
            ]);

            // Third Enter Data in responder_students Table
            

            if ($user) {
                $response['data']['message'] = 'Request Successfull';
                $response['data']['error'] = 200;
                $response['data']['result'] = $user->getArrayResponse();
                $response['status'] = true;
            }
        }
        return $response;
    }

}
