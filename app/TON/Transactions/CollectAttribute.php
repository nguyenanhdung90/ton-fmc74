<?php

namespace App\TON\Transactions;


class CollectAttribute implements CollectAttributeInterface
{
    protected CollectAttributeInterface $component;

    public function __construct(CollectAttributeInterface $component)
    {
        $this->component = $component;
    }

    public function collect(array $data): array
    {
        return $this->component->collect($data);
    }
}
