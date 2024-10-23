<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets;

class TransferOptions
{
    public ?int $seqno;

    public int $timeout;

    /**
     * @param int|null $seqno Seqno. Set `0` to initialize wallet when making transfers
     */
    public function __construct(
        ?int $seqno = null,
        int $timeout = 60
    ) {
        $this->seqno = $seqno;
        $this->timeout = $timeout;
    }
}
