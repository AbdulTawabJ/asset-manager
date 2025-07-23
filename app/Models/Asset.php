<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'asset_tag', 'date_of_purchase', 'date_of_issue', 'type', 'description',
        'amount', 'location', 'owner', 'remarks', 'remarked_by',
        'requires_it_remark', 'status',
    ];
}
