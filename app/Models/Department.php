<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    public $timestamps = false;

    protected $primaryKey = 'department';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['department'];
}
