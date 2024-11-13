<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CoinInfoAddress extends Model
{
    const ENVIRONMENT_MAIN = 'MAIN';
    const ENVIRONMENT_TEST = 'TEST';
    protected $table = 'coin_info_address';

    protected $fillable = [
        'currency', 'hex_master_address', 'environment'
    ];

    public function coin_info(): HasOne
    {
        return $this->hasOne(CoinInfo::class, 'currency', 'currency')
            ->where("is_active", 1);
    }
}
