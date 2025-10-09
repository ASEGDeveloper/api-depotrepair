<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TnaEntry extends Model
{
    use HasFactory;

    protected $table = 'deporepair.tna_entry_dublicate'; // schema.table (SQL Server)

    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'COMPANYCODE',
        'EMPLOYEECODE',
        'JOBCODE',
        'JOBSEQNO',
        'EXPORTFLAG',
        'OPST',
        'PROJECTEDENDDATE',
        'PROJECTEDENDTIME',
        'OR_UPD_FLG',
        'TAS_DATA_FROM',
        'ENTRY_MODE',
        'IS_MANUAL',
        'SD',
        'ED'
    ];
}
