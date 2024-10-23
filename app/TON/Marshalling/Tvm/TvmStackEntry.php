<?php declare(strict_types=1);

namespace App\TON\Marshalling\Tvm;

abstract class TvmStackEntry
{
    protected string $type;

    protected $data;

    public function __construct(
        string $type,
        $data
    )
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData()
    {
        return $this->data;
    }
}
