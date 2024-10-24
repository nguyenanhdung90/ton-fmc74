<?php /** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpPropertyOnlyWrittenInspection */
declare(strict_types=1);

namespace App\TON\TypedArrays;

/**
 * @property-read ArrayBuffer buffer
 * @property-read int byteOffset
 * @property-read int byteLength
 */
abstract class ArrayBufferView
{
    /**
     * @var ArrayBuffer
     */
    protected $buffer;

    /**
     * @var ArrayBuffer
     */
    protected $byteOffset;

    /**
     * @var int
     */
    protected $byteLength;

    /**
     * @throws \Exception
     */
    public function __get(string $propertyName)
    {
        if ($propertyName === "buffer") {
            return $this->buffer;
        } else if ($propertyName === "byteOffset") {
            return $this->byteOffset;
        } else if ($propertyName === "byteLength") {
            return $this->byteLength;
        }

        throw new \Exception(self::class . " has no such property '$propertyName'");
    }
}
