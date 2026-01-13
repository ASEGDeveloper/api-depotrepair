<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TnaEntry extends Model
{
    use HasFactory;

    protected $table = 'deporepair.tna_entries_uat'; // schema.table (SQL Server)

   // protected $table = 'deporepair.tna_entry_duplicate'; // schema.table (SQL Server)


    protected $primaryKey = 'ID';
    
     // ✅ Enable timestamps
    public $timestamps = true;

    // ✅ Map Laravel timestamps to SQL Server columns
    const CREATED_AT = 'CREATED_ON';
    const UPDATED_AT = 'UPDATED_ON';
    

    protected $fillable = [
        'COMPANYCODE',
        'EMPLOYEECODE',
        'JOBCODE',
        'STARTDATE',
        'STARTTIME',
        'ENDDATE',
        'ENDTIME',
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
        'ED',
        'CREATED_BY',
        'CREATED_ON',
        'UPDATED_BY',
        'UPDATED_ON',
        'LOGIN_ID',
];




}
