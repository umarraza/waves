<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalResource extends Model
{
    //Status Constants
	const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'local_resources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'insuranceType',
        'streetAddress',
        'city',
        'state',
        'zipCode',
        'phoneNumber',
        'website',
        'schoolProfileId',
        'serviceTypeId',
    ];
}
