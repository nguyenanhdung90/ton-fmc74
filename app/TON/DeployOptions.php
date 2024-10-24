<?php declare(strict_types=1);

namespace App\TON;

use Brick\Math\BigInteger;
use App\TON\Contracts\Wallets\Wallet;
use App\TON\TypedArrays\Uint8Array;

class DeployOptions
{
    public Wallet $deployerWallet;
    public Uint8Array $deployerSecretKey;
    public BigInteger $storageAmount;

    public function __construct(
        Wallet $deployerWallet,
        Uint8Array $deployerSecretKey,
        BigInteger $storageAmount
    ) {
        $this->deployerSecretKey = $deployerWallet;
        $this->deployerSecretKey = $deployerSecretKey;
        $this->storageAmount = $storageAmount;
    }
}
