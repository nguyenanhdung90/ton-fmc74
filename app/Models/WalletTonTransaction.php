<?php

namespace App\Models;

use App\TON\Transactions\TransactionHelper;
use Illuminate\Database\Eloquent\Model;

class WalletTonTransaction extends Model
{
    protected $table = 'wallet_ton_transactions';

    protected $fillable = [
        'from_address_wallet', 'from_memo', 'type', 'to_memo', 'to_address_wallet', 'hash', 'in_msg_hash',
        'amount', 'currency', 'total_fees', 'lt', 'decimals', 'query_id', 'is_sync_amount', 'is_sync_total_fees'
    ];

    public function needSyncExcess(): bool
    {
        return $this->type === TransactionHelper::WITHDRAW_EXCESS && (!$this->is_sync_amount || !$this->is_sync_total_fees);
    }

    public function needSyncWithdrawTon(): bool
    {
        return $this->type === TransactionHelper::WITHDRAW && $this->currency === TransactionHelper::TON && (!$this->is_sync_amount || !$this->is_sync_total_fees);
    }

    public function needSyncWithdrawJetton(): bool
    {
        return $this->type === TransactionHelper::WITHDRAW && $this->currency !== TransactionHelper::TON && (!$this->is_sync_amount || !$this->is_sync_total_fees);
    }

    public function needSyncDeposit(): bool
    {
        return $this->type === TransactionHelper::DEPOSIT && (!$this->is_sync_amount || !$this->is_sync_total_fees);
    }
}
