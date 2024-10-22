<?php declare(strict_types=1);

namespace App\TON\Marshalling\Tvm;

// {
// '@type': 'tvm.stackEntrySlice',
// 'slice': {
//  '@type': 'tvm.Slice',
//  'bytes': "base64 BoC"
//  }
// }

class Slice extends TvmStackEntry
{
    public function __construct(\App\TON\Interop\Boc\Slice $data)
    {
        parent::__construct("tvm.stackEntrySlice", $data);
    }

    public function getData(): \App\TON\Interop\Boc\Slice
    {
        return $this->data;
    }
}
