<?php

namespace Plumthedev\JsonShape\Concerns;

use Illuminate\Database\Eloquent\Casts\Json;

/** @template T of array<string, mixed> */
trait HasFactories
{
    /**
     * @phpstan-param  T $attributes
     *
     * @return static<T>
     */
    public static function make(array $attributes): static
    {
        return new static($attributes);
    }

    /** @return static<T> */
    public static function fromJson(string $value): static
    {
        /** @var T $decoded */
        $decoded = Json::decode($value);

        return new static($decoded);
    }

    /** @return static<array{}> */
    public static function empty(): static
    {
        return new static([]);
    }
}
