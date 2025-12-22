<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemModel extends Model
{
    use HasFactory;

    protected $table = 'deporepair.items_dpr';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'CustomerID',
        'ItemNumber',
        'Description',
        'PRIMARY_UOM_CODE',
        'PURCHASING_ITEM_FLAG',
        'SHIPPABLE_ITEM_FLAG',
        'CUSTOMER_ORDER_FLAG',
        'INTERNAL_ORDER_FLAG',
        'INV_ITEM_FLAG',
        'ENG_ITEM_FLAG',
        'SERVICE_ITEM_FLAG',
        'INVENTORY_ASSET_FLAG',
        'PURENAB_FLAG',
        'CUST_ORG_ENAB_FLAG',
        'INT_ORDER_ENAB_FLAG',
        'SO_TRX_FLAG',
        'MTL_TRX_ENAB_FLAG',
        'STOCK_ENAB_FLAG',
        'BOM_ENAB_FLAG',
        'INSPECT_REQ_FLAG',
        'RECEIPT_REQ_FLAG',
        'INV_ITEM_STATUS_CODE',
        'SERV_BIL_FLAG',
        'MATERIAL_BILLABLE',
        'OrganizationID',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'CustomerID', 'ID');
    }
}
