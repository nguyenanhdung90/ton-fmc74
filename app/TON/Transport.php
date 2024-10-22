<?php declare(strict_types=1);

namespace App\TON;

use Brick\Math\BigNumber;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Contracts\Messages\ExternalMessage;
use App\TON\Contracts\Messages\ResponseStack;
use App\TON\Exceptions\TransportException;
use App\TON\Marshalling\Tvm\TvmStackEntry;
use App\TON\TypedArrays\Uint8Array;

interface Transport
{
    /**
     * @param array[]|TvmStackEntry[] $stack
     * @throws TransportException
     */
    public function runGetMethod($contract, string $method, array $stack = []): ResponseStack;

    /**
     * @throws TransportException
     */
    public function send($boc): void;

    /**
     * @throws TransportException
     */
    public function sendMessage(ExternalMessage $message, Uint8Array $secretKey): void;

    /**
     * @throws TransportException
     */
    public function estimateFee(Address $address,
                                $body,
                                $initCode = null,
                                $initData = null): BigNumber;

    /**
     * @throws TransportException
     */
    public function getConfigParam(int $configParamId): Cell;

    /**
     * @throws TransportException
     */
    public function getState(Address $address): AddressState;
}
