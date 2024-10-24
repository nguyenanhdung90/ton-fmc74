<?php declare(strict_types=1);

namespace App\TON\Interop\Boc;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class DictSerializers
{
    private $keySerializer;

    private bool $isDefKeySerializer = false;

    private $keyDeserializer;

    private bool $isDefKeyDeserializer = false;

    private $valueSerializer;

    private bool $isDefValueSerializer = false;

    private $valueDeserializer;

    private bool $isDefValueDeserializer = false;

    private bool $isCombined = false;

    public final function __construct(
        ?callable $keySerializer = null,
        ?callable $keyDeserializer = null,
        ?callable $valueSerializer = null,
        ?callable $valueDeserializer = null
    ) {
        $this->keySerializer = $keySerializer ?? static fn ($key) => $key;
        $this->isDefKeySerializer = !$keySerializer;

        $this->keyDeserializer = $keyDeserializer ?? static fn($key) => $key;
        $this->isDefKeyDeserializer = !$keyDeserializer;

        $this->valueSerializer = $valueSerializer ?? static fn ($value) => $value;
        $this->isDefValueSerializer = !$valueSerializer;

        $this->valueDeserializer = $valueDeserializer ?? static fn($value) => $value;
        $this->isDefValueDeserializer = !$valueDeserializer;
    }

    public final function combine(DictSerializers $serializers): self
    {
        if ($this->isCombined) {
            throw new \RuntimeException("Already combined with other Serializer");
        }

        $this->isCombined = true;
        $callbacks = [
            [
                $this->isDefKeySerializer,
                $serializers->getKeySerializer(),
                'keySerializer',
            ],
            [
                $this->isDefKeyDeserializer,
                $serializers->getKeyDeserializer(),
                'keyDeserializer',
            ],
            [
                $this->isDefValueSerializer,
                $serializers->getValueSerializer(),
                'valueSerializer',
            ],
            [
                $this->isDefValueDeserializer,
                $serializers->getValueDeserializer(),
                'valueDeserializer',
            ],
        ];

        foreach ($callbacks as [$isDefault, $callback, $property]) {
            if ($isDefault) {
                $this->{$property} = $callback;
            }
        }

        return $this;
    }

    public final static function uintKey(
        bool $isBigInt = true,
        ?callable $valueSerializer = null,
        ?callable $valueDeserializer = null
    ): self
    {
        return new self(
            static fn ($k, int $keySize): array => BitString::empty()
                ->writeUint($k, $keySize)
                ->toBitsA(),
            static function (array $k, int $keySize) use ($isBigInt) {
                $key = (new Builder())
                    ->writeBitArray($k)
                    ->cell()
                    ->beginParse()
                    ->loadUint($keySize);

                return $isBigInt ? $key : $key->toInt();
            },
            $valueSerializer,
            $valueDeserializer,
        );
    }

    public final static function intKey(
        bool $isBigInt = true,
        ?callable $valueSerializer = null,
        ?callable $valueDeserializer = null
    ): self
    {
        return new self(
            static fn ($k, int $keySize): array => BitString::empty()
                ->writeInt($k, $keySize)
                ->toBitsA(),
            static function (array $k, int $keySize) use ($isBigInt) {
                $key = (new Builder())
                    ->writeBitArray($k)
                    ->cell()
                    ->beginParse()
                    ->loadInt($keySize);

                return $isBigInt ? $key : $key->toInt();
            },
            $valueSerializer,
            $valueDeserializer,
        );
    }

    public final static function addressKey(
        ?callable $valueSerializer = null,
        ?callable $valueDeserializer = null
    ): self
    {
        return new self(
            static function (?Address $k, int $keySize): array {
                if ($keySize !== 267) {
                    throw new \InvalidArgumentException();
                }
                return BitString::empty()
                    ->writeAddress($k)
                    ->toBitsA();
            },
            static function (array $k, int $keySize): Address {
                return (new Builder())
                    ->writeBitArray($k)
                    ->cell()
                    ->beginParse()
                    ->loadAddress();
            },
            $valueSerializer,
            $valueDeserializer,
        );
    }

    public final static function sha256stringKey(
        ?callable $valueSerializer = null,
        ?callable $valueDeserializer = null
    ): self
    {
        return new self(
            static fn (string $key, int $keySize): array => BitString::empty()
                ->writeUint(BigInteger::fromBytes(hash("sha256", $key, true), false), $keySize)
                ->toBitsA(),
            null,
            $valueSerializer,
            $valueDeserializer,
        );
    }

    public final static function intValue(
        int $intSize,
        bool $isBigInt = true,
        ?callable $keySerializer = null,
        ?callable $keyDeserializer = null
    ): self
    {
        return new self(
            $keySerializer,
            $keyDeserializer,
            static fn($v): Cell => (new Builder())->writeInt($v, $intSize)->cell(),
            static function (Cell $v) use($isBigInt, $intSize) {
                $value = $v
                    ->beginParse()
                    ->loadInt($intSize);

                return $isBigInt ? $value : $value->toInt();
            },
        );
    }

    public final static function uintValue(
        int $uintSize,
        bool $isBigInt = true,
        ?callable $keySerializer = null,
        ?callable $keyDeserializer = null
    ): self
    {
        return new self(
            $keySerializer,
            $keyDeserializer,
            static fn ($v): Cell => (new Builder())->writeUint($v, $uintSize)->cell(),
            static function (Cell $v) use($isBigInt, $uintSize) {
                $value = $v
                    ->beginParse()
                    ->loadUint($uintSize);

                return $isBigInt ? $value : $value->toInt();
            },
        );
    }

    public final static function snakeStringValue(
        ?callable $keySerializer = null,
        ?callable $keyDeserializer = null
    ): self
    {
        return new self(
            $keySerializer,
            $keyDeserializer,
            static fn (string $v): Cell => SnakeString::fromString($v)->cell(),
            static fn (Cell $v): string => SnakeString::parse($v)->getData(),
        );
    }

    public final static function onchainMetadata(): self
    {
        return self::snakeStringValue()->combine(self::sha256stringKey());
    }

    public final function getKeySerializer(): callable
    {
        return $this->keySerializer;
    }

    public final function getKeyDeserializer(): callable
    {
        return $this->keyDeserializer;
    }

    public final function getValueSerializer(): callable
    {
        return $this->valueSerializer;
    }

    public final function getValueDeserializer(): callable
    {
        return $this->valueDeserializer;
    }

    public final function setKeySerializer(callable $keySerializer): void
    {
        $this->keySerializer = $keySerializer;
        $this->isDefKeySerializer = false;
    }

    public final function setKeyDeserializer(callable $keyDeserializer): void
    {
        $this->keyDeserializer = $keyDeserializer;
        $this->isDefKeySerializer = false;
    }

    public final function setValueSerializer(callable $valueSerializer): void
    {
        $this->valueSerializer = $valueSerializer;
        $this->isDefValueSerializer = false;
    }

    public final function setValueDeserializer(callable $valueDeserializer): void
    {
        $this->valueDeserializer = $valueDeserializer;
        $this->isDefKeyDeserializer = false;
    }
}
