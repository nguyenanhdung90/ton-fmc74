<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTonMemo extends Model
{
    use HasFactory;

    protected $table = 'wallet_ton_memos';

    protected $fillable = [
        'memo', 'currency', 'amount'
    ];
}
