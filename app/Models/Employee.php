<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    public $timestamps = false;

    protected $primaryKey = 'file_no';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'file_no', 'first_name', 'middle_name', 'last_name',
        'email', 'department'
    ];
}
