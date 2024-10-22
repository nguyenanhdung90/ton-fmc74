<?php declare(strict_types=1);

namespace App\TON\Transports\NullTransport;

use Brick\Math\BigNumber;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Ton\AddressState;
use App\TON\Ton\Contract;
use App\TON\Contracts\Exceptions\ContractException;
use App\TON\Contracts\Messages\ExternalMessage;
use App\TON\Contracts\Messages\ResponseStack;
use App\TON\Exceptions\TransportException;
use App\TON\Transport;
use App\TON\Transports\Toncenter\ToncenterResponseStack;
use App\TON\TypedArrays\Uint8Array;

class NullTransport implements Transport
{
    public function runGetMethod($contract, string $method, array $stack = []): ResponseStack
    {
        try {
            if ($contract instanceof Contract) {
                $contract->getAddress();
            }
        // @codeCoverageIgnoreStart
        } catch (ContractException $e) {
            throw new TransportException(
                "Address error: " . $e->getMessage(),
                0,
                $e,
            );
        }
        // @codeCoverageIgnoreEnd

        return ToncenterResponseStack::empty();
    }

    public function send($boc): void
    {
        // Nothing
    }

    public function sendMessage(ExternalMessage $message, Uint8Array $secretKey): void
    {
        // Nothing
    }

    public function estimateFee(Address $address,
                                $body,
                                $initCode = null,
                                $initData = null): BigNumber
    {
        return BigNumber::of(0);
    }

    public function getConfigParam(int $configParamId): Cell
    {
        return new Cell();
    }

    public function getState(Address $address): AddressState
    {
        return AddressState::UNINITIALIZED;
    }
}
