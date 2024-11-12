<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'user_name', 'amount', 'currency', 'decimals', 'is_active'
    ];

    public function walletMemo(): BelongsTo
    {
        return $this->belongsTo(WalletMemo::class, 'user_name', 'user_name');
    }
}
