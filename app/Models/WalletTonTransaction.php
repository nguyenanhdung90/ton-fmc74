<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTonTransaction extends Model
{
    protected $table = 'wallet_ton_transactions';

    protected $fillable = [
        'from_address_wallet', 'from_memo', 'type', 'to_memo', 'hash', 'amount', 'currency', 'total_fees', 'lt'
        , 'decimals'
    ];
}
