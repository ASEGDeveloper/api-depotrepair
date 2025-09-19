<?php
 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerModel extends Model
{
    use HasFactory;

    protected $table = 'customers_dpr';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'CustomerName',
        'CustomerNumber',
        'AccountID',
        'TRN',
        'LocationNumber',
        'AccountNumber',
    ];

    // Relationships
    public function sites()
    {
        return $this->hasMany(CustomerSiteModel::class, 'CustomerID', 'ID');
    }

    public function items()
    {
        return $this->hasMany(ItemModel::class, 'CustomerID', 'ID');
    }
}
