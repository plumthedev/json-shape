<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Illuminate\Support\Traits as IlluminateConcerns;
use JsonSerializable;

/**
 * @template TData of array<string, mixed>
 *
 * @implements Arrayable<string, mixed>
 * @implements ArrayAccess<string, mixed>
 */
class JsonShape implements Arrayable, ArrayAccess, Jsonable, JsonSerializable
{
    use Concerns\HasArrayAccess;

    /** @use Concerns\HasFactories<TData> */
    use Concerns\HasFactories;

    use IlluminateConcerns\Conditionable;
    use IlluminateConcerns\Dumpable;
    use IlluminateConcerns\Macroable;
    use IlluminateConcerns\Tappable;

    /** @var Fluent<string, mixed> */
    protected Fluent $fluent {
        get {
            return Fluent::make($this->attributes);
        }
    }

    /** @phpstan-param TData $attributes */
    final protected function __construct(protected array $attributes) {}

    /**
     * @template TAttrs of array<string, mixed>
     *
     * @param  TAttrs|self<TData>  $shape
     */
    public function merge(array|self $shape): static
    {
        /** @var TData $attributes */
        $attributes = Arr::undot(array_merge(
            Arr::dot($this->attributes),
            Arr::dot($shape instanceof self ? $shape->attributes : $shape),
        ));

        return tap($this, fn () => $this->attributes = $attributes);
    }

    /** @return static<TData> */
    public function clone(): static
    {
        return new static($this->attributes);
    }

    /** @return TData */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /** @return TData */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson($options = 0): mixed
    {
        return Json::encode($this->jsonSerialize(), $options); // @phpstan-ignore return.type
    }
}
