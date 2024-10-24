<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTonWithdraw extends Model
{
    protected $table = 'wallet_ton_withdraws';

    protected $fillable = [
        'to_address_wallet', 'currency', 'amount', 'decimals', 'transaction_id'
    ];
}
