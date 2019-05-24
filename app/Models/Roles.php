<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class User.
 */
class Roles extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description'];

    public static function findByAttr($attr,$label){
        $model = self::where($attr,'=',$label)->first();
        if(!empty($model))
            return $model;

        return '';
    }
}
