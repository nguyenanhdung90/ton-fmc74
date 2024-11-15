<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JettonTransfer extends Model
{
    protected $table = 'jetton_transfers';

    protected $fillable = [
        'query_id', 'trace_id', 'lt', 'jetton_master'
    ];
}
