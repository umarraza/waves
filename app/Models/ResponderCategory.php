<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponderCategory extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'responder_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['schoolProfileId','levelId','positionName'];
}
