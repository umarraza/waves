<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefferalRequest extends Model
{
    //Status Constants
	const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schoolProfileId',
        'refferedBy',
        'refferedTo',
        'studentId',
        'description',
        'status',
    ];
}
