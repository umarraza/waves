<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('auth/login', 'Api\AuthController@login');
Route::get('auth/get-roles', 'Api\AuthController@getRoles');
Route::post('auth/create-admin', 'Api\AuthController@createAdmin');
Route::get('school/listwt', 'Api\SchoolController@listwt');
Route::post('signup', 'Api\AuthController@signup');

Route::group(['middleware' => 'jwt.auth'], function () {
	Route::post('auth/create-school', 'Api\SchoolController@createSchool');
	Route::get('auth/school/list', 'Api\SchoolController@list');
	Route::post('auth/school/edit', 'Api\SchoolController@edit');
	Route::post('auth/school/delete', 'Api\SchoolController@delete');
    Route::get('auth/logout', 'Api\AuthController@logout');
    Route::get('auth/is-valid', 'Api\AuthController@isValidToken');
    Route::post('import/local-resource', 'Api\ImportDataController@localResource');
    Route::post('import/crisis-resource', 'Api\ImportDataController@crisisResource');
    Route::post('import/responder', 'Api\ImportDataController@responder');
    Route::post('import/student', 'Api\ImportDataController@student');
    Route::get('export/students', 'Api\ExportDataController@students');
    Route::post('chat/get-messages', 'Api\ChatController@get');
    Route::post('chat/save-messages', 'Api\ChatController@save');
    Route::get('chat/get-threads', 'Api\ChatController@getThreads');
    Route::post('chat/create-threads', 'Api\ChatController@createThread');
    Route::post('chat/mass-messenger', 'Api\ChatController@massMessenger');
    Route::post('chat/mark-messages', 'Api\ChatController@markMessages');
    Route::post('auth/update-online-status', 'Api\AuthController@updateOnlineStatus');
    Route::post('schedule/create', 'Api\ScheduleController@create');
    Route::get('schedule/list', 'Api\ScheduleController@list');
    Route::post('schedule/delete', 'Api\ScheduleController@delete');
    Route::post('auth/update-user-device', 'Api\AuthController@updateUserDevice');
    Route::post('schedule/update', 'Api\ScheduleController@update');
    Route::post('chat/get-length', 'Api\ChatController@getLength');



    Route::post('chat/mass-messenger-admin', 'Api\ChatController@massMessengerAdmin'); 

  	// ------------------------------------------------------------------------------

	// Students
	Route::post('addStudents', 'Api\StudentController@createStudents');		// Add Students
	Route::post('editStudents', 'Api\StudentController@editStudents');		// Edit Students
	Route::post('delStudents', 'Api\StudentController@delStudents');		// Dell Students
	Route::post('getStudents', 'Api\StudentController@getStudents');		// Get Students
	
	// Get specific responder Students
	Route::post('get-students-of-responder', 'Api\StudentController@StudentAssignToResponder');

	// local Resource
	Route::post('getLocalResource', 'Api\LocalResourceController@getLocalResources'); 		// To Get
	Route::post('addLocalResource', 'Api\LocalResourceController@addLocalResources');		// To Add
	Route::post('editLocalResource', 'Api\LocalResourceController@editLocalResources');		// To Edit
	Route::post('delLocalResource', 'Api\LocalResourceController@delLocalResources');		// To Del

	// crisis Resource
	Route::post('getCrisisResource', 'Api\CrisisResourceController@getCrisisResources'); 	// To Get
	Route::post('addCrisisResource', 'Api\CrisisResourceController@addCrisisResources');	// To Add
	Route::post('editCrisisResource', 'Api\CrisisResourceController@editCrisisResources');	// To Edit
	Route::post('delCrisisResource', 'Api\CrisisResourceController@delCrisisResources');	// To Del

	// Responder
	Route::post('getResponder', 'Api\ResponderController@getResponder'); 	// To Get
	Route::post('auth/responder/add-responder', 'Api\ResponderController@addResponder');	// To Add
	Route::post('auth/responder/edit-responder', 'Api\ResponderController@editResponder'); 	// To Edit
	Route::post('auth/responder/delete-responder','Api\ResponderController@deleteResponder'); // To Del
	Route::post('get-responder-except', 'Api\ResponderController@getResponderForResponder'); 	// To Get all responder except the one who request
	Route::post('get-responder-only-counselor', 'Api\ResponderController@getResponderCounselor'); 	// To Get only counselor
	

	Route::get('get-responder-levels', 'Api\ResponderController@getResponderCategories'); 	// To Get Levels


	Route::post('reSend-email-responder', 'Api\ResponderController@reSendEmailResponder'); 	// To Re Send Email Responder
	Route::post('reSend-email-student', 'Api\StudentController@reSendEmailStudent'); 	// To Re Send Email Student



	Route::post('get-alerts', 'Api\ScheduleAlertController@getFutureAlerts'); 		// get alerts
	Route::post('edit-alert', 'Api\ScheduleAlertController@editAlert'); 			// edit alert
	Route::post('delete-alert', 'Api\ScheduleAlertController@deleteAlert'); 		// delete alert
	

	

	// Get specific Student responder
	Route::post('get-responder-of-student', 'Api\ResponderController@ResponderAssignToStudent');

	

	// Send Feedback
	Route::post('send-feedback', 'Api\FeedBackController@sendFeedback');

	Route::post('change-DP', 'Api\ImageController@storeDP');	// Cahnge Dp
	Route::post('show-DP', 'Api\ImageController@showDP');		// Show Dp	


	// Change Password
	Route::post('change-password', 'Api\PasswordController@changePassword');



	// Reffer Apis
	Route::post('delete-requests', 'Api\RefferController@delRequests');
	Route::post('approve-requests', 'Api\RefferController@approveRequests');
	Route::post('new-requests', 'Api\RefferController@getNewRequests');
	Route::post('reffer-new-responder', 'Api\RefferController@RefferNewResponder');


	Route::post('remove-anonimity', 'Api\AnonimityController@sendAnonimityRequest');
Route::post('get-remove-anonimity-reports', 'Api\AnonimityController@getAnonimityRequest');
Route::post('del-anonimity-reports', 'Api\AnonimityController@delAnonimityRequest');
Route::post('approve-anonimity-reports', 'Api\AnonimityController@approveAnonimityRequest');	
});


// Forgot Password
Route::post('forgot-password', 'Api\PasswordController@forgotPassword');	
// Change Password During SignUp
Route::post('change-password-signUp', 'Api\PasswordController@changePasswordSignUp');






// Haseeb Routes
Route::get('getHaseeb/{id}', 'HaseebController@get');	// Get 
Route::post('addHaseeb', 'HaseebController@add');		// Add










Route::post('new-schedule', 'Api\ScheduleController@NewShedule');






Route::get('get-alerts', 'Api\ScheduleAlertController@getFutureAlerts'); 		// get alerts