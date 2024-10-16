<?php declare(strict_types=1);

namespace App\TON\Interop\Boc;

use App\TON\Interop\Boc\Exceptions\BitStringException;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Interop\Boc\Exceptions\HashmapException;
use App\TON\Interop\Boc\Exceptions\SliceException;

class HashmapE extends Hashmap
{
    protected function serialize(): Cell
    {
        try {
            $nodes = $this->getSortedHashmap();
            $result = new Builder();

            if (empty($nodes)) {
                return $result->writeBit(0)->cell();
            }

            return $result
                ->writeBit(1)
                ->writeRef(self::serializeEdge($nodes))
                ->cell();
        } catch (BitStringException $e) {
            throw new HashmapException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function parse(int $keySize, Cell $cell, ?DictSerializers $serializers = null): self
    {
        try {
            $slice = $cell->beginParse();

            if (!$slice->loadBit()) {
                return new self($keySize, $serializers);
            }

            $instance = new self($keySize, $serializers);
            $nodes = self::deserializeEdge($slice->loadRef(), $keySize);

            for ($i = 0; $i < count($nodes); $i++) {
                [$key, $value] = $nodes[$i];

                $instance->setRaw($key, $value);
            }

            return $instance;
        } catch (CellException|SliceException $e) {
            throw new HashmapException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
