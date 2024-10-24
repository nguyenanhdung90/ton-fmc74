<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use Brick\Math\BigInteger;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\BitStringException;
use App\TON\Contracts\Messages\Exceptions\MessageException;

class ExternalOutMessage extends Message
{
    /**
     * @throws MessageException
     */
    public function __construct(ExternalOutMessageOptions $options, ?MessageData $data = null)
    {
        $src = $options->src;
        $dest = $options->dest;
        $createdLt = BigInteger::of($options->createdLt ?? "0");
        $createdAt = BigInteger::of($options->createdAt ?? "0");

        $body = $data?->body;
        $state = $data?->state;

        $header = new Cell();
        $headerBs = $header->bits;

        try {
            $headerBs
                ->writeBit(1)
                ->writeBit(1)
                ->writeAddress($src)
                ->writeAddress($dest)
                ->writeUint($createdLt, 64)
                ->writeUint($createdAt, 32);

        } catch (BitStringException $e) {
            throw new MessageException($e->getMessage(), $e->getCode(), $e);
        }


        parent::__construct($header, $body, $state);
    }
}
