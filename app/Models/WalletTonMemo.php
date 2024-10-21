<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTonMemo extends Model
{
    protected $table = 'wallet_ton_memos';

    protected $fillable = [
        'memo', 'currency', 'amount', 'decimals'
    ];
}
