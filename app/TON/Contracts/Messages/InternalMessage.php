<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use Brick\Math\BigInteger;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\BitStringException;
use App\TON\Interop\Units;
use App\TON\Contracts\Messages\Exceptions\MessageException;

class InternalMessage extends Message
{
    /**
     * @throws MessageException
     */
    public function __construct(InternalMessageOptions $options, ?MessageData $data = null)
    {
        $ihrDisabled = is_null($options->ihrDisabled) ? true : $options->ihrDisabled;
        $bounce = $options->bounce;
        $bounced = is_null($options->bounced) ? false : $options->bounced;
        $src = $options->src;
        $dest = $options->dest;
        $value = $options->value;
        $ihrFee = is_null($options->ihrFee) ? Units::toNano(0) : $options->ihrFee;
        $fwdFee = is_null($options->fwdFee) ? Units::toNano(0) : $options->fwdFee;
        $createdLt = BigInteger::of($options->createdLt ?? "0");
        $createdAt = BigInteger::of($options->createdAt ?? "0");

        $body = empty($data) ? null : $data->body;
        $state = empty($data) ? null : $data->state;

        $header = new Cell();
        $headerBs = $header->bits;

        try {
            $headerBs
                ->writeBit(0)
                ->writeInt($ihrDisabled ? -1 : 0, 1)
                ->writeInt($bounce ? -1 : 0, 1)
                ->writeInt($bounced ? -1 : 0, 1)
                ->writeAddress($src)
                ->writeAddress($dest)
                ->writeCoins($value)
                ->writeBit(0)
                ->writeCoins($ihrFee)
                ->writeCoins($fwdFee)
                ->writeUint($createdLt, 64)
                ->writeUint($createdAt, 32);
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new MessageException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd

        parent::__construct($header, $body, $state);
    }
}
