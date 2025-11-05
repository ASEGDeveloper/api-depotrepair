<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class CustomerModel extends Model
{
    use HasFactory;

    protected $table = 'customers_dpr';
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
            $username = Auth::check() ? Auth::user()->name : 'system';
            $model->CreatedBy = $username;
            $model->UpdatedBy = $username;
        });

        static::updating(function ($model) {
            $username = Auth::check() ? Auth::user()->name : 'system';
            $model->UpdatedBy = $username;
        });
    }
}
