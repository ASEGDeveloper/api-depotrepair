<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class CustomerSiteModel extends Model
{
    use HasFactory;

    protected $table = 'deporepair.sites_dpr';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'Customer_ID',       // foreign key to customers table
        'CustomerSite',
        'SiteAddress',       // nvarchar(MAX)
        'BillTo',            // BIT (true/false)
        'ShipTo',            // BIT (true/false)
        'EnabledFlag',
        'CreatedBy',
        'UpdatedBy',
        'DataLoaded_Time'
    ];

    protected $casts = [
        'BillTo' => 'boolean',
        'ShipTo' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'Customer_ID', 'ID');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $username = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->CreatedBy = $username;
            $model->UpdatedBy = $username;
        });

        static::updating(function ($model) {
            $username = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->UpdatedBy = $username;
        });
    }

}
