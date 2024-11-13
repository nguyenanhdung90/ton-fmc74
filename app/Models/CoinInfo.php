<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CoinInfo extends Model
{
    protected $table = 'coin_infos';

    protected $fillable = [
        'name', 'description', 'image', 'currency', 'decimals', 'is_active'
    ];

    public function coin_info_address(): HasOne
    {
        $environment = config("services.ton.is_main") ? CoinInfoAddress::ENVIRONMENT_MAIN :
            CoinInfoAddress::ENVIRONMENT_TEST;
        return $this->hasOne(CoinInfoAddress::class, 'currency', 'currency')
            ->where("environment", $environment);
    }
}
