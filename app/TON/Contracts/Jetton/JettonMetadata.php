<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

class JettonMetadata implements \JsonSerializable
{
    public string $name;
    public string $description;
    public string $symbol;
    public ?string $imageData;
    public int $decimals;
    public ?string $image;

    public function __construct(
        string $name,
        string $description,
        string $symbol,
        ?string $imageData,
        int $decimals = 9,
        ?string $image = null
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->symbol = $symbol;
        $this->imageData = $imageData;
        $this->decimals = $decimals;
        $this->image = $image;
    }

    public function jsonSerialize(): array
    {
        return [
            "name" => $this->name,
            "description" => $this->description,
            "symbol" => $this->symbol,
            "image_data" => $this->imageData,
            "decimals" => $this->decimals,
        ];
    }

    /**
     * @param array{name: string, description: string, symbol: string, image_data: string, decimals: int|string}|string $json
     * @throws \JsonException
     */
    public static function fromJson($json): self
    {
        if (is_string($json)) {
            $json = json_decode($json, true, JSON_THROW_ON_ERROR);
        }

        if (empty($json["name"])) {
            throw new \InvalidArgumentException("`name` is required");
        }
        $name = $json["name"];

        if (empty($json["description"])) {
            throw new \InvalidArgumentException("`description` is required");
        }
        $description = $json["description"];

        if (empty($json["symbol"])) {
            throw new \InvalidArgumentException("`symbol` is required");
        }
        $symbol = $json["symbol"];

        $imageData = $json["image_data"] ?? null;
        $image = $json["image"] ?? null;
        if (empty($json["decimals"])) {
            throw new \InvalidArgumentException("`decimals` is required");
        }
        $decimals = $json["decimals"];

        return new self(
            $name,
            $description,
            $symbol,
            $imageData,
            (int)$decimals,
            $image,
        );
    }
}
