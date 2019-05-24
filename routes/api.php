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
    Route::post('auth/logout', 'Api\AuthController@logout');
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
    Route::post('schedule/create', 'Api\ScheduleController@createSession');
    Route::get('schedule/list', 'Api\ScheduleController@listSession');
    Route::post('schedule/delete', 'Api\ScheduleController@deleteSession');
    Route::post('auth/update-user-device', 'Api\AuthController@updateUserDevice');
    Route::post('schedule/update', 'Api\ScheduleController@updateSession');
    Route::post('chat/get-length', 'Api\ChatController@getLength');

    Route::get('splash/login', 'Api\AuthController@splashLogin');

    
    Route::post('schedule/create2', 'Api\ScheduleController@createSession');
    Route::get('schedule/list2', 'Api\ScheduleController@listSession');


    Route::post('reported/analytics', 'Api\AnonimityController@analytics');
    Route::post('refferal/analytics', 'Api\RefferController@analytics');
    Route::post('student/analytics', 'Api\StudentController@analytics');
    Route::post('schedule/analytics', 'Api\ScheduleController@analytics');
    Route::post('chat/analytics', 'Api\ChatController@analytics');
    Route::post('waves/analytics', 'Api\WavesController@analytics');

    Route::post('threadWaves/update', 'Api\ChatController@updateThreadWaves');

    Route::post('get-online-status', 'Api\ResponderController@getOnlineStatus');


    Route::post('refferReportedCount', 'Api\RefferController@adminSideBarRequestsCount');
    Route::post('waves/list', 'Api\WavesController@listWaves');


    Route::post('waves/add', 'Api\WavesController@addWaves');
    Route::post('waves/edit', 'Api\WavesController@editWaves');
    Route::post('waves/delete', 'Api\WavesController@delWaves');

    Route::post('mark-reported-read', 'Api\AnonimityController@markReportedRead');


    Route::post('chat/mass-messenger-admin', 'Api\ChatController@massMessengerAdmin'); 
    Route::post('chat/mass-messenger-employee', 'Api\ChatController@massMessengerEmployee'); 



    Route::post('/edit-school-name', 'Api\SchoolController@editSchoolName');
    Route::post('/edit-school-address', 'Api\SchoolController@editSchoolAddress');
    Route::post('/edit-school-timezone', 'Api\SchoolController@editSchoolTimeZone');
    Route::post('/edit-school-logo', 'Api\SchoolController@editSchoolLogo');
    Route::post('/edit-school-admin-name', 'Api\SchoolController@editSchoolAdminName');
    Route::post('/edit-school-admin-email', 'Api\SchoolController@editSchoolAdminEmail'); 
    Route::post('/total-student-count', 'Api\SchoolController@getTotalStudentsCount'); 


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
	Route::post('get-responder-except', 'Api\ResponderController@getResponderForResponder');// To Get all responder except the one who request
	Route::post('get-responder-only-counselor', 'Api\ResponderController@getResponderCounselor'); 	// To Get only counselor
	

	Route::post('get-responder-levels', 'Api\ResponderController@getResponderCategories'); 	// To Get Levels
	Route::post('edit-responder-levels', 'Api\ResponderController@editResponderCategories'); // To edit Levels
	Route::post('delete-responder-levels', 'Api\ResponderController@deleteResponderCategories'); // To edit Levels
	Route::post('add-responder-category', 'Api\ResponderController@addResponderCategory');  // To add category



	// Re Send Mails
	Route::post('reSend-email-responder', 'Api\ResponderController@reSendEmailResponder'); 	// To Re Send Email Responder
	Route::post('reSend-email-student', 'Api\StudentController@reSendEmailStudent'); 		// To Re Send Email Student



	// Secondary Admin

	Route::post('add-secondary-admin', 'Api\SchoolSecondaryAdminProfileController@createSecondaryAdmin');	// To Add Secondary Admin
	Route::post('delete-secondary-admin', 'Api\SchoolSecondaryAdminProfileController@deleteSecondaryAdmin');// To Delete Secondary Admin
	Route::post('get-secondary-admin', 'Api\SchoolSecondaryAdminProfileController@getSecondaryAdmin'); 		// To Get Secondary Admin
	Route::post('edit-secondary-admin', 'Api\SchoolSecondaryAdminProfileController@editSecondaryAdmin'); 	// To Edit Secondary Admin




	// Mass Alerts
	Route::post('get-alerts', 'Api\ScheduleAlertController@getFutureAlerts'); 		// get alerts
	Route::post('edit-alert', 'Api\ScheduleAlertController@editAlert'); 			// edit alert
	Route::post('delete-alert', 'Api\ScheduleAlertController@deleteAlert'); 		// delete alert
	

	
	Route::post('get-responder-position-name', 'Api\ResponderController@getResponderPosition');
	// Get specific Student responder
	Route::post('get-responder-of-student', 'Api\ResponderController@ResponderAssignToStudent');
	Route::post('get-responder-not-of-student', 'Api\ResponderController@ResponderNotAssignToStudent');
	Route::post('get-responder-not-of-student-ref', 'Api\ResponderController@ResponderNotAssignToStudentRef');

	

	// Send Feedback
	Route::post('send-feedback', 'Api\FeedBackController@sendFeedback');

	Route::post('change-DP', 'Api\ImageController@storeDP');	// Cahnge Dp
	Route::post('show-DP', 'Api\ImageController@showDP');		// Show Dp
	Route::post('remove-DP', 'Api\ImageController@removeDP');	// Remove Dp
	Route::post('remove-DP-school', 'Api\ImageController@removeDPSchool');	// Remove Dp	School


	// Change Password
	Route::post('change-password', 'Api\PasswordController@changePassword');



	// Reffer Apis
	Route::post('delete-requests', 'Api\RefferController@delRequests');
	Route::post('approve-requests', 'Api\RefferController@approveRequests');
	Route::post('new-requests', 'Api\RefferController@getNewRequests');
	Route::post('reffer-new-responder', 'Api\RefferController@RefferNewResponder');

	Route::get('exportCrisisSupport', 'Api\ExportDataController@crisisSupport');	// Get
	Route::get('exportLocalResource', 'Api\ExportDataController@LocalResource');	// Get
	Route::get('exportStudent', 'Api\ExportDataController@student');	// Get
	Route::get('exportResponder', 'Api\ExportDataController@responder');	// Get


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



Route::get('exportTest', 'Api\ExportDataController@index');	// Get





Route::post('new-schedule', 'Api\ScheduleController@NewShedule');



Route::get('schedule/test', 'Api\ScheduleController@sentNotificationsAA');
Route::get('alert/test', 'Api\ScheduleAlertController@sentNotificationsA');










Route::get('get-remove-anonimity-reports', 'Api\AnonimityController@getAnonimityRequestT');