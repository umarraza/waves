<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waves extends Model
{

	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'waves';

    //Attributes Constants
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'name',
    ];
}
