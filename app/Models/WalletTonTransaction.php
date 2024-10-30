<?php

namespace App\Models;

use App\TON\Transactions\TransactionHelper;
use Illuminate\Database\Eloquent\Model;

class WalletTonTransaction extends Model
{
    protected $table = 'wallet_ton_transactions';

    protected $fillable = [
        'from_address_wallet', 'from_memo', 'type', 'to_memo', 'to_address_wallet', 'hash', 'in_msg_hash',
        'amount', 'currency', 'total_fees', 'lt', 'decimals', 'query_id', 'is_sync_amount_ton', 'is_sync_amount_jetton'
    ];

    public function isSyncExcess(): bool
    {
        return $this->type === TransactionHelper::WITHDRAW_EXCESS && $this->is_sync_amount_ton;
    }

    public function isSyncWithdrawTon(): bool
    {
        return $this->type === TransactionHelper::WITHDRAW && $this->is_sync_amount_ton;
    }

    public function isSyncWithdrawJetton(): bool
    {
        return $this->type === TransactionHelper::WITHDRAW && $this->currency !== TransactionHelper::TON &&
            $this->is_sync_amount_ton && $this->is_sync_amount_jetton;
    }

    public function isSyncDeposit(): bool
    {
        if ($this->currency === TransactionHelper::TON) {
            return $this->type === TransactionHelper::DEPOSIT && $this->is_sync_amount_ton;
        } else {
            return $this->type === TransactionHelper::DEPOSIT && $this->is_sync_amount_ton
                && $this->is_sync_amount_jetton;
        }
    }
}
