<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InstallBaseItemModel extends Model
{
    protected $table = 'deporepair.installbase_items_dpr';
    protected $primaryKey = 'ID';

    public $timestamps = true;

    // Correct timestamp column names
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [ 
        'Item_Numbers',
        'Serial_Numbers',
        'installbase_id',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
        'isDeleted',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : null;

            $model->created_by = $userID;
            $model->updated_by = $userID;
        });

        static::updating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : null;

            $model->updated_by = $userID;
        });
    }
}
