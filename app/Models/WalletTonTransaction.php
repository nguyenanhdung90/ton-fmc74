<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTonTransaction extends Model
{
    protected $table = 'wallet_ton_transactions';

    protected $fillable = [
        'from_address_wallet', 'from_memo', 'type', 'to_memo', 'to_address_wallet', 'hash', 'in_msg_hash',
        'amount', 'currency', 'total_fees_of_ton', 'fixed_fee', 'lt', 'decimals', 'query_id', 'is_sync_amount',
        'status', 'is_sync_total_fees_of_ton', 'is_sync_fixed_fee'
    ];
}
