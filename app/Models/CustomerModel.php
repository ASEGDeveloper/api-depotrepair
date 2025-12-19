<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class CustomerModel extends Model
{
    use HasFactory;

    protected $table = 'deporepair.customers_dpr';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'OrganizationID',
        'CustomerName',
        'CustomerNumber',
        'AccountID',
        'PaymentTermID',
        'TRN',
        'PaymentTerms',
        'LocationNumber',
        'CreatedBy',
        'UpdatedBy',
    ];

    /**
     * Automatically set CreatedBy and UpdatedBy fields
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->CreatedBy = $userID;
            $model->UpdatedBy = $userID;
        });

        static::updating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->UpdatedBy = $userID;
        });
    }
}
