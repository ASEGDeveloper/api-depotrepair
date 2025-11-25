<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemMasterModel extends Model
{
    use HasFactory;

    protected $table = 'item_master_dpr';
    protected $primaryKey = 'ID';

    // Disable default timestamp handling since we have custom fields
    public $timestamps = false;

    // Define the fillable fields based on the table columns
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

            // Optionally set Creation_date and Updated_date
            $model->Creation_date = now();
            $model->Updated_date = now();
        });

        static::updating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->Updated_by = $userID;

            // Update Updated_date automatically
            $model->Updated_date = now();
        });
    }
}
