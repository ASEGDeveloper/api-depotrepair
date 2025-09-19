<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerSiteModel extends Model
{
    use HasFactory;

    protected $table = 'customer_sites_dpr';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'CustomerID',
        'SiteCode',
        'SiteAddress',
        'SiteUseCode',
        'PaymentTermID',
        'PaymentTerm',
        'PaymentTermDesc',
        'Address1',
        'Address2',
        'Address3',
        'Address4',
        'City',
        'State',
        'Postcode',
        'PartyName',
        'PartyShipToLocation',
        'ShipToAddress',
        'CounterName',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'CustomerID', 'ID');
    }
}
