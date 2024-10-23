<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter\Responses;

class UnrecognizedSmcRunResult
{
    const MAP_JSON = [
        'type' => "@type",
        'gasUsed' => "gas_used",
        'exitCode' => "exit_code",
        'extra' => "@extra",
        'stack' => "stack",
    ];

    public string $type;

    public int $gasUsed;

    public int $exitCode;

    public string $extra;

    public array $stack;
}
