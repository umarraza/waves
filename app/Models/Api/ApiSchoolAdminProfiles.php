<?php

/* Folder Location */

namespace App\Models\Api;

/* Dependencies */

use App\Models\SchoolAdminProfiles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApiSchoolProfiles.
 */
class ApiSchoolAdminProfiles extends SchoolAdminProfiles {
    
    public function sendEmailFeedback($text)
    {
        \Mail::send('vendor.mail.html.waves.feedback', [
                'name' => $this->fullName(),
                'roleName' => $this->user->role->description,
                'feedback' => $text,
                'schoolName' => $this->schoolProfile->schoolName,
            ], function ($message) {
                $message->from('muhammadhaseebsohail@gmail.com', 'The Waves Team');
                $message->to('haseeb.sohail@ucp.edu.pk')->subject('Feedback');
        });
        return true;
    }
}