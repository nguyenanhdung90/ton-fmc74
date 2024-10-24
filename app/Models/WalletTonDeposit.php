<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTonDeposit extends Model
{
    protected $table = 'wallet_ton_deposits';

    protected $fillable = [
        'memo', 'currency', 'amount', 'decimals', 'transaction_id'
    ];
}
