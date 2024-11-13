<?php

namespace App\Models;

use App\TON\TonHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CoinInfoAddress extends Model
{
    protected $table = 'coin_info_address';

    protected $fillable = [
        'currency', 'hex_master_address', 'environment'
    ];

    public function coin_info(): HasOne
    {
        return $this->hasOne(CoinInfo::class, 'currency', 'currency')
            ->where("is_active", TonHelper::ACTIVE);
    }
}
