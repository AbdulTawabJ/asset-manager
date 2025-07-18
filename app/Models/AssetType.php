<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    protected $table = 'asset_types';
    public $timestamps = false;

    protected $primaryKey = 'type';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['type'];
}
