<?php

namespace Plumthedev\JsonShape\Exceptions;

use Plumthedev\JsonShape\JsonShape;
use RuntimeException;

class JsonShapeException extends RuntimeException
{
    /** @param array<string, mixed> $context */
    public function __construct(string $message, private readonly array $context = [])
    {
        parent::__construct($message);
    }

    /**
     * @template T of JsonShape
     *
     * @param  T  $shape
     */
    public static function missingKey(string|int $key, JsonShape $shape): self
    {
        return new self(sprintf('Undefined key [%s] for shape [%s]', $key, $shape::class), context: [
            'shape' => $shape->toArray(),
        ]);
    }

    /** @return array<string, mixed> */
    public function getContext(): array
    {
        return $this->context;
    }
}
