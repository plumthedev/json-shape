<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Plumthedev\JsonShape\JsonShape;

/** @template T of JsonShape */
class AsJsonShape implements Castable
{
    /**
     * @param  array{0?: class-string<T>}  $arguments
     * @return CastsAttributes<T, T|null>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class($arguments) implements CastsAttributes
        {
            private string $shapeClass;

            /** @param array{0?: class-string<T>} $arguments */
            public function __construct(array $arguments = [])
            {
                $this->shapeClass = ($arguments[0] ?? null) ?: JsonShape::class;
            }

            /** @phpstan-return null|T */
            public function get(Model $model, string $key, mixed $value, array $attributes): ?JsonShape
            {
                $data = Json::decode($attributes[$key]);

                if (! is_array($data)) {
                    return null;
                }

                return $this->shapeClass::make($data);
            }

            /** @return array<string, mixed> */
            public function set(Model $model, string $key, mixed $value, array $attributes): array
            {
                return [$key => $value === null ? null : Json::encode($value)];
            }
        };
    }

    /**
     * @param  class-string<T>  $shapeClass
     */
    public static function of(string $shapeClass): string
    {
        return static::class.':'.implode(',', [$shapeClass]);
    }
}
