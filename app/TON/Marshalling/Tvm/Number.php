<?php declare(strict_types=1);

namespace App\TON\Marshalling\Tvm;

use Brick\Math\BigInteger;

// {
// '@type': 'tvm.stackEntryNumber',
// 'number': {
//  '@type': 'tvm.numberDecimal',
//  'number': "1000"
//  }
// }

class Number extends TvmStackEntry
{
    public function __construct($data)
    {
        parent::__construct("tvm.stackEntryNumber", $data);
    }

    public function getData()
    {
        return $this->data;
    }
}
