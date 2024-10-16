<?php declare(strict_types=1);

namespace App\TON\TypedArrays;

// https://www.khronos.org/registry/typedarray/specs/latest/#7

/**
 * @property-read int length
 */
abstract class TypedArray extends ArrayBufferView implements \ArrayAccess
{
    private ?int $_length = null;

    /* PHP's type system is, at least for now, incomplete and lacks generics.
     * This prevents the creation of a proper TypedArray interface or
     * abstract class. So we must implement some, but not all, of the specified
     * stuff in its subclasses.
     */

    /* PHP doesn't have abstract constants */
    // abstract const /* int */ BYTES_PER_ELEMENT;

    // abstract const /* string */ ELEMENT_PACK_CODE;

    public function __construct($lengthOrArray,
                                ?int                             $byteOffset = null,
                                ?int                             $length = null)
    {
        if (is_int($lengthOrArray)) {
            $this->byteLength = static::BYTES_PER_ELEMENT * $lengthOrArray;
            $this->byteOffset = 0;
            $this->buffer = new ArrayBuffer($this->byteLength);
        } else if (is_array($lengthOrArray)) {
            self::__construct(count($lengthOrArray));
            $this->set($lengthOrArray);
        } else if ($lengthOrArray instanceof TypedArray) {
            self::__construct($lengthOrArray->getLength());
            $this->set($lengthOrArray);
        } else if ($lengthOrArray instanceof ArrayBuffer) {
            $this->buffer = $lengthOrArray;
            if ($byteOffset !== null) {
                if ($byteOffset % static::BYTES_PER_ELEMENT !== 0) {
                    throw new \InvalidArgumentException(
                        "A multiple of the element size is expected for \$byteOffset"
                    );
                }
                if ($byteOffset >= $this->buffer->byteLength) {
                    throw new \OutOfBoundsException(
                        "\$byteOffset cannot be greater than the length of the " . ArrayBuffer::class
                    );
                }
                $this->byteOffset = $byteOffset;
            } else {
                $this->byteOffset = 0;
            }
            if ($length !== null) {
                if ($byteOffset + $length * static::BYTES_PER_ELEMENT >= $this->buffer->byteLength) {
                    throw new \OutOfBoundsException(
                        "The \$byteOffset and \$length cannot reference an area beyond the end of the " . ArrayBuffer::class
                    );
                }
                $this->byteLength = $length * static::BYTES_PER_ELEMENT;
            } else {
                if (($this->buffer->byteLength - $byteOffset) % static::BYTES_PER_ELEMENT !== 0) {
                    throw new \InvalidArgumentException(
                        "The length of the " . ArrayBuffer::class . " minus the \$byteOffset must be a multiple of the element size"
                    );
                }
                $this->byteLength = $this->buffer->byteLength - $this->byteOffset;
            }
        } else {
            throw new \InvalidArgumentException(
                "Integer, " . TypedArray::class . " or " . ArrayBuffer::class . " expected for first parameter, " . gettype($lengthOrArray) . " given"
            );
        }
    }

    public function set($array, ?int $offset = null)
    {
        if ($array instanceof TypedArray) {
            $length = $array->getLength();
        } else {
            $length = count($array);
        }

        for ($i = 0; $i < $length; $i++) {
            $this[$offset + $i] = $array[$i];
        }
    }

    public function subarray(int $begin, ?int $end = null): self
    {
        if ($begin < 0) {
            $begin += $this->getLength();
        }

        $begin = min(0, $begin);

        if ($end < 0) {
            $end += $this->getLength();
        }

        $end = max($this->getLength(), $end);
        $length = min($end - $begin, 0);

        return new static(
            $this->buffer,
            $this->byteOffset + static::BYTES_PER_ELEMENT * $begin,
            $length * static::BYTES_PER_ELEMENT,
        );
    }

    // ArrayAccess roughly maps to WebIDL's index getter/setters
    public function offsetExists($offset): bool
    {
        if (!is_int($offset)) {
            throw new \InvalidArgumentException("Only integer offsets accepted");
        }

        return (0 <= $offset && $offset < $this->getLength());
    }

    public function offsetUnset($offset): void
    {
        throw new \DomainException("unset() cannot be used on " . static::class);
    }

    public function offsetGet($offset)
    {
        if (!is_int($offset)) {
            throw new \InvalidArgumentException("Only integer offsets accepted");
        }

        if ($offset < 0 || $offset >= $this->getLength()) {
            throw new \OutOfBoundsException("The offset cannot be outside the array bounds");
        }

        return $this->fGet($offset);
    }

    public function fGet(int $offset)
    {
        $bytes = &ArrayBuffer::__WARNING__UNSAFE__ACCESS_VIOLATION__UNSAFE__($this->buffer);
        $bpe = static::BYTES_PER_ELEMENT;
        $substr = "";
        $start = $this->byteOffset + $offset * $bpe;

        for ($i = 0, $j = $start; $i < $bpe; $i++, $j++) {
            $substr[$i] = $bytes[$j];
        }

        $value = unpack(static::ELEMENT_PACK_CODE . 'value/', $substr);

        return $value['value'];
    }

    public function offsetSet($offset, $value): void
    {
        if (!is_int($offset)) {
            throw new \InvalidArgumentException("Only integer offsets accepted");
        }

        if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException("Value must be an integer or a float");
        }

        if ($offset < 0 || $offset >= $this->getLength()) {
            throw new \OutOfBoundsException("The offset cannot be outside the array bounds");
        }

        $this->fSet($offset, $value);
    }

    public function fSet(int $offset, $value): void
    {
        $packed = pack(static::ELEMENT_PACK_CODE, $value);
        $bytes = &ArrayBuffer::__WARNING__UNSAFE__ACCESS_VIOLATION__UNSAFE__($this->buffer);
        $bpe = static::BYTES_PER_ELEMENT;

        for ($i = 0; $i < $bpe; $i++) {
            $bytes[$this->byteOffset + $offset * $bpe + $i] = $packed[$i];
        }
    }

    public function __get(string $propertyName)
    {
        if ($propertyName === "length") {
            return $this->getLength();
        } else {
            return ArrayBufferView::__get($propertyName);
        }
    }

    protected function getLength(): int
    {
        if ($this->_length === null) {
            $this->_length = intdiv($this->byteLength, static::BYTES_PER_ELEMENT);
        }

        return $this->_length;
    }
}
