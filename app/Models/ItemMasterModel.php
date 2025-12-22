<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemMasterModel extends Model
{
    use HasFactory;

    protected $table = 'deporepair.item_master_dpr';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    
    // Define your actual timestamp column names
    const CREATED_AT = 'Creation_date';  // or whatever the actual column name is
    const UPDATED_AT = 'Updated_date';   // or whatever the actual column name is

    protected $fillable = [
        'InventoryItemID',
        'ItemNumber',
        'TankType',
        'Manufacturer',
        'UnPortableTankType',
        'Capacity',
        'Description',
        'PrimaryUOM',
        'MAWP',
        'PurchasingFLAG',
        'OrganizationID',
        'Created_by',
        'Updated_by',
        'DATALOAD_TIME'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->Created_by = $userID;
            $model->Updated_by = $userID;
        });

        static::updating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->Updated_by = $userID;
        });
    }
}