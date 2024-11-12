<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletMemo extends Model
{
    protected $table = 'wallet_memos';

    protected $fillable = [
        'memo', 'user_name'
    ];

    public function wallet(): HasMany
    {
        return $this->hasMany(Wallet::class, 'user_name', 'user_name');
    }
}
