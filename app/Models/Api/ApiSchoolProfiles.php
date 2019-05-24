<?php

/* Folder Location */

namespace App\Models\Api;

/* Dependencies */

use App\Models\SchoolProfiles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApiSchoolProfiles.
 */
class ApiSchoolProfiles extends SchoolProfiles {
    
    public function generateAccessCode(){
    	$explode = explode(' ', $this->schoolName);
    	$this->accessCode = '';
    	foreach ($explode as $key => $value) {
    		$this->accessCode .= $value[0];
    		if(strlen($this->accessCode) >= 3)
    			break;
    	}
    	$this->accessCode = strtoupper($this->accessCode) . rand ( 1000 , 9999 );
    }
}