<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'user_name', 'amount', 'currency', 'is_active'
    ];

    public function walletMemo(): HasOne
    {
        return $this->hasOne(WalletMemo::class, 'user_name', 'user_name');
    }

    public function coinInfo(): HasOne
    {
        return $this->hasOne(CoinInfo::class, 'currency', 'currency');
    }
}
