<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class SerializationTest extends TestCase
{
    public function test_it_implements_the_serialization_contracts(): void
    {
        $shape = JsonShape::empty();

        $this->assertInstanceOf(Arrayable::class, $shape);
        $this->assertInstanceOf(Jsonable::class, $shape);
        $this->assertInstanceOf(JsonSerializable::class, $shape);
    }

    public function test_to_array_returns_the_underlying_attributes(): void
    {
        $attributes = ['traceId' => 'abc-123', 'context' => ['userId' => 'u-1']];

        $shape = JsonShape::make($attributes);

        $this->assertSame($attributes, $shape->toArray());
    }

    public function test_json_serialize_matches_to_array(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc-123']);

        $this->assertSame($shape->toArray(), $shape->jsonSerialize());
    }

    public function test_to_json_encodes_the_attributes(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc-123', 'duration' => 504]);

        $this->assertSame('{"traceId":"abc-123","duration":504}', $shape->toJson());
    }

    public function test_to_json_forwards_encoding_options(): void
    {
        $shape = JsonShape::make(['path' => 'a/b']);

        $this->assertSame('{"path":"a\/b"}', $shape->toJson());
        $this->assertSame('{"path":"a/b"}', $shape->toJson(JSON_UNESCAPED_SLASHES));
    }

    public function test_json_encode_uses_json_serialize(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc-123']);

        $this->assertSame('{"traceId":"abc-123"}', json_encode($shape));
    }
}
