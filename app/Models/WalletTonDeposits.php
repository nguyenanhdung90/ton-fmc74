<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTonDeposits extends Model
{
    protected $table = 'wallet_ton_memos';

    protected $fillable = [
        'memo', 'currency', 'amount', 'decimals', 'transaction_id'
    ];
}
