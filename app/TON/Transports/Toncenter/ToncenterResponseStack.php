<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter;

use Brick\Math\BigInteger;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Contracts\Messages\Exceptions\ResponseStackParsingException;
use App\TON\Contracts\Messages\ResponseStack;

class ToncenterResponseStack  extends \SplQueue implements ResponseStack
{
    private const TYPE_NUM = "num";

    private const TYPE_LIST = "list";

    private const TYPE_TUPLE = "tuple";

    private const TYPE_CELL = "cell";

    private ?array $rawStack;

    /**
     * @throws ResponseStackParsingException
     */
    public static function parse(array $rawStack): self
    {
        $instance = new self();
        $instance->rawStack = $rawStack;
        $instance->parseInternal($rawStack);

        return $instance;
    }

    /**
     * @throws ResponseStackParsingException
     */
    protected function parseInternal(array $rawStack): void
    {
        foreach ($rawStack as $idx => [$typeName, $value]) {
            switch ($typeName) {
                case self::TYPE_NUM:
                    $this->push([
                        $typeName,
                        BigInteger::fromBase(
                            str_replace("0x", "", $value),
                            16,
                        ),
                    ]);
                    break;

                case self::TYPE_LIST:
                case self::TYPE_TUPLE:
                    $this->push([
                        $typeName,
                        array_map(static fn (array $entry) => self::parseObject($entry), $value["elements"]),
                    ]);
                    break;

                case self::TYPE_CELL:
                    try {
                        $this->push([
                            $typeName,
                            Cell::oneFromBoc($value["bytes"], true),
                        ]);
                    } catch (CellException $e) {
                        throw new ResponseStackParsingException(
                            sprintf(
                                "Cell deserialization error: %s; stack index: %u",
                                $e->getMessage(),
                                $idx,
                            ),
                            $e->getCode(),
                            $e,
                        );
                    }
                    break;

                default:
                    throw new ResponseStackParsingException(
                        "Unknown type: " . $typeName,
                    );
            }
        }

        $this->rewind();
    }

    public function currentBigInteger(): ?BigInteger
    {
        return $this->currentInternal(self::TYPE_NUM);
    }

    public function currentList(): ?array
    {
        return $this->currentInternal(self::TYPE_LIST);
    }

    public function currentTuple(): ?array
    {
        return $this->currentInternal(self::TYPE_TUPLE);
    }

    public function currentCell(): ?Cell
    {
        return $this->currentInternal(self::TYPE_CELL);
    }

    public function current()
    {
        $curr = parent::current();

        if (is_array($curr)) {
            [$type, $currentValue] = $curr;

            return $currentValue;
        }

        return null;
    }

    public static function empty(): self
    {
        return new self();
    }

    protected function __construct() {}

    /**
     * @throws ResponseStackParsingException
     */
    private static function parseObject(array $entry): mixed
    {
        $typeName = $entry["@type"];

        try {
            switch ($typeName) {
                case "tvm.list":
                case "tvm.tuple":
                    return array_map(static fn(array $e) => self::parseObject($e), $entry["elements"]);
                case "tvm.cell":
                    return Cell::oneFromBoc($entry["bytes"], true);
                case "tvm.slice":
                    return Cell::oneFromBoc($entry["bytes"], true)->beginParse();
                case "tvm.stackEntryCell":
                    return self::parseObject($entry["cell"]);
                case "tvm.stackEntryTuple":
                    return self::parseObject($entry["tuple"]);
                case "tvm.stackEntryNumber":
                    return self::parseObject($entry["number"]);
                case "tvm.stackEntrySlice":
                    return self::parseObject($entry["slice"]);
                case "tvm.numberDecimal":
                    return BigInteger::fromBase($entry["number"], 10);
                default:
                    throw new ResponseStackParsingException(
                        "Unknown type: " . $typeName,
                    );
            }

        } catch (CellException $e) {
            throw new ResponseStackParsingException(
                sprintf(
                    "Cell deserialization error: %s; type: %s",
                    $e->getMessage(),
                    $typeName,
                ),
                $e->getCode(),
                $e,
            );
        }

    }

    private function currentInternal(string $type)
    {
        $current = parent::current();

        if (is_array($current)) {
            [$currentType, $currentValue] = $current;

            if ($currentType === $type) {
                return $currentValue;
            }
        }

        return null;
    }

    public function __serialize(): array
    {
        return [
            "raw" => $this->rawStack,
        ];
    }

    /**
     * @throws ResponseStackParsingException
     */
    public function __unserialize($data): void
    {
        $rawStack = $data["raw"];
        $this->rawStack = $rawStack;
        $this->parseInternal($rawStack);
    }
}
