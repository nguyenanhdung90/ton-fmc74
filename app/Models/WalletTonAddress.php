<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTonAddress extends Model
{
    protected $table = 'wallets_ton_address';

    protected $fillable = [
        'memo', 'currency', 'amount', 'decimals'
    ];
}
