<?php

namespace App\Models;
// app/Models/Employee.php
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    use HasApiTokens;
    
    //protected $table = 'deporepair.employee';

    protected $table = 'deporepair.employee';

    protected $primaryKey = 'ID';   // 👈 must match DB column name
    public $incrementing = true;            // 👈 true if auto-increment
    protected $keyType = 'int';   

    
    protected $fillable = [
        'ID','EmployeeName', 'EmployeeEmail', 'EmployeePassword'
    ];

    protected $hidden = [
        'EmployeePassword',
    ];

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }


    /**
     * Override the tokens() relationship to use the schema-qualified token model
     */
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }
    
}