<?php
namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class PersonalAccessToken extends SanctumToken
{
    // Explicitly specify the schema-qualified table name
   // protected $table = 'deporepair.personal_access_tokens';
   protected $table = 'personal_access_tokens';
}