<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinInfo extends Model
{
    protected $table = 'coin_infos';

    protected $fillable = [
        'name', 'description', 'image', 'currency', 'decimals', 'is_active'
    ];
}
