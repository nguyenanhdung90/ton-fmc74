<?php declare(strict_types=1);

namespace App\TON\TypedArrays;

// https://www.khronos.org/registry/typedarray/specs/latest/#5
// Note that Transferable is NOT implemented here, because it isn't useful for
// PHP

/**
 * @property-read int byteLength
 */
class ArrayBuffer
{
    private string $bytes;

    public function __construct(int $length)
    {
        $this->bytes = str_repeat("\x00", $length);
    }

    public function slice(int $begin, ?int $end = null): self
    {
        $newBuffer = new self(0);

        if ($begin < 0) {
            $begin += $this->byteLength;
        }

        if ($end !== null) {
            if ($end < 0) {
                $end += $this->byteLength;
            }

            $newBuffer->bytes = substr($this->bytes, $begin, max(0, $end - $begin));
        } else {
            $newBuffer->bytes = substr($this->bytes, $begin);
        }

        return $newBuffer;
    }

    public static function isView($value): bool
    {
        return $value instanceof ArrayBufferView;
    }

    /**
     * @throws \Exception
     */
    public function __get(string $propertyName)
    {
        if ($propertyName === "byteLength") {
            return strlen($this->bytes);
        }

        throw new \Exception(self::class . " has no such property '$propertyName'");
    }

    // ABADnON alL hooPE yE Wh0 EnteR HERe
    // ThE fORgEOTTEn OnE TheEY COMETh
    // and in the end there is but peace
    /**
     * @ignore
     */
    public static function &__WARNING__UNSAFE__ACCESS_VIOLATION__UNSAFE__(self $buffer): string
    {
        // tbf'u V jvfu CUC unq
        // sevraq py'nffrf fb V
        // jbhyqa'g arrq gb qb guvf
        return $buffer->bytes;
    }
}
