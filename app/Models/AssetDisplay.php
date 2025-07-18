<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDisplay extends Model
{
    protected $table = 'asset_display';

    // No primary key in views usually
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
// protected $guarded = []; // allow mass assignment if needed
}

