<?php
// app/Models/AssetHistory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    protected $table = 'asset_history';

    protected $fillable = [
        'serial_no',
        'description',
        'prev_location',
        'new_location',
        'prev_owner',
        'new_owner',
        'remarks',
        'remarked_by',
        'requires_it_remark',
        'date',
    ];

    public $timestamps = false;
}
