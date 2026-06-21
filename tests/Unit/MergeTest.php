<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class MergeTest extends TestCase
{
    public function test_it_merges_an_array(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc-123']);

        $shape->merge(['message' => 'hello']);

        $this->assertSame(['traceId' => 'abc-123', 'message' => 'hello'], $shape->toArray());
    }

    public function test_it_merges_another_shape(): void
    {
        $base = JsonShape::make(['traceId' => 'abc-123']);
        $other = JsonShape::make(['message' => 'hello']);

        $base->merge($other);

        $this->assertSame(['traceId' => 'abc-123', 'message' => 'hello'], $base->toArray());
    }

    public function test_it_merges_using_dot_notation(): void
    {
        $shape = JsonShape::make(['context' => ['userId' => 'u-1', 'levelNumber' => 1]]);

        $shape->merge(['context.userId' => 'u-2']);

        $this->assertSame(
            ['context' => ['userId' => 'u-2', 'levelNumber' => 1]],
            $shape->toArray(),
        );
    }

    public function test_dot_notation_creates_missing_nested_keys(): void
    {
        $shape = JsonShape::empty();

        $shape->merge(['context.userId' => 'superSimple']);

        $this->assertSame(['context' => ['userId' => 'superSimple']], $shape->toArray());
    }

    public function test_it_overwrites_existing_top_level_values(): void
    {
        $shape = JsonShape::make(['traceId' => 'old']);

        $shape->merge(['traceId' => 'new']);

        $this->assertSame(['traceId' => 'new'], $shape->toArray());
    }

    public function test_it_returns_the_same_instance_for_chaining(): void
    {
        $shape = JsonShape::empty();

        $returned = $shape
            ->merge(['context.userId' => 'superSimple'])
            ->merge(JsonShape::make(['message' => 'one']))
            ->merge(JsonShape::make(['traceId' => 'two']));

        $this->assertSame($shape, $returned);
        $this->assertSame([
            'context' => ['userId' => 'superSimple'],
            'message' => 'one',
            'traceId' => 'two',
        ], $shape->toArray());
    }

    public function test_it_keeps_the_concrete_shape_type(): void
    {
        $shape = TraceShape::empty();

        $this->assertInstanceOf(TraceShape::class, $shape->merge(['traceId' => 'abc']));
    }
}
