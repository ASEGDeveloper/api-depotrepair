<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InstallBaseModel extends Model
{
    protected $table = 'installbase_dpr';
    protected $primaryKey = 'ID';

    public $timestamps = true;

    // Match your SQL Server column names
    const CREATED_AT = 'Creation_date';
    const UPDATED_AT = 'Updated_date';

    protected $fillable = [ 
        'Customer_Name',
        'CustomerID',
        'Created_by',
        'Creation_date',
        'Updated_by',
        'Updated_date',
        'DATALOAD_TIME',
    ];

    /**
     * Automatically set Created_by and Updated_by fields
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->Created_by = $userID;
            $model->Updated_by = $userID;
            $model->Creation_date = now();
            $model->Updated_date = now();
            $model->DATALOAD_TIME = now();
        });

        static::updating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->Updated_by = $userID;
            $model->Updated_date = now();
        });
    }
}
