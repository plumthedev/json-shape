<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\Concerns\HasFactories;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(HasFactories::class)]
#[CoversClass(JsonShape::class)]
final class FactoriesTest extends TestCase
{
    public function test_make_builds_a_shape_from_an_array(): void
    {
        $shape = JsonShape::make(['name' => 'json-shape']);

        $this->assertInstanceOf(JsonShape::class, $shape);
        $this->assertSame(['name' => 'json-shape'], $shape->toArray());
    }

    public function test_make_preserves_the_concrete_shape_class(): void
    {
        // A partial payload — deliberately not the full shape.
        $shape = TraceShape::make(['traceId' => 'abc-123']); // @phpstan-ignore argument.type

        $this->assertInstanceOf(TraceShape::class, $shape);
        $this->assertSame('abc-123', $shape->traceId);
    }

    public function test_from_json_decodes_a_raw_json_string(): void
    {
        $json = '{"traceId":"abc-123","context":{"userId":"u-1","levelNumber":1}}';

        $shape = TraceShape::fromJson($json);

        $this->assertSame('abc-123', $shape->traceId);
        $this->assertSame(['userId' => 'u-1', 'levelNumber' => 1], $shape->getContext());
    }

    public function test_from_json_returns_the_concrete_shape_class(): void
    {
        $shape = TraceShape::fromJson('{"traceId":"x"}');

        $this->assertInstanceOf(TraceShape::class, $shape);
    }

    public function test_empty_creates_a_blank_shape(): void
    {
        $shape = JsonShape::empty();

        $this->assertSame([], $shape->toArray());
    }

    public function test_empty_returns_the_concrete_shape_class(): void
    {
        $shape = TraceShape::empty();

        $this->assertInstanceOf(TraceShape::class, $shape);
        $this->assertSame([], $shape->toArray());
    }
}
