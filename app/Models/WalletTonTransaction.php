<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTonTransaction extends Model
{
    protected $table = 'wallet_ton_transactions';

    protected $fillable = [
        'from_address_wallet', 'from_memo', 'type', 'to_memo', 'to_address_wallet', 'hash', 'in_msg_hash',
        'amount', 'currency', 'occur_ton', 'lt', 'query_id', 'is_sync_amount',
        'status', 'is_sync_occur_ton', 'fixed_fee', 'is_sync_fixed_fee'
    ];
}
