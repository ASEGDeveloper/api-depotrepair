<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $fillable = ['employee_id', 'token', 'expires_at'];

   // protected $dates = ['expires_at'];

   protected $casts = [
        'expires_at' => 'datetime', // ensures it is Carbon instance
    ];
    

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'ID');  
    }
}
