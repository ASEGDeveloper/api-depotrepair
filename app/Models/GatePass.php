<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatePass extends Model
{
    use HasFactory;

    protected $table = 'deporepair.gate_pass';

    protected $primaryKey = 'ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_ON';
    const UPDATED_AT = 'UPDATED_ON';

    protected $fillable = [
        'gate_pass_no',
        'COMPANYCODE',
        'EMPLOYEECODE',
        'JOBCODE',
        'CREATED_BY',
        'CREATED_ON',
        'UPDATED_BY',
        'UPDATED_ON',
    ];
}
