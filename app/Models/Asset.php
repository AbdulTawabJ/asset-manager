<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'serial_no', 'date_of_purchase', 'type', 'description',
        'amount', 'location', 'owner', 'remarks', 'remarked_by',
        'requires_it_remark'
    ];
}
