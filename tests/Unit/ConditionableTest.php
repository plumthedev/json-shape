<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class ConditionableTest extends TestCase
{
    public function test_when_runs_the_callback_on_a_truthy_condition(): void
    {
        $shape = JsonShape::empty();

        $shape->when(true, fn (JsonShape $shape) => $shape->merge(['examples.whenPassed' => true]));

        $this->assertSame(['examples' => ['whenPassed' => true]], $shape->toArray());
    }

    public function test_when_skips_the_callback_on_a_falsy_condition(): void
    {
        $shape = JsonShape::empty();

        $shape->when(false, fn (JsonShape $shape) => $shape->merge(['examples.whenPassed' => true]));

        $this->assertSame([], $shape->toArray());
    }

    public function test_unless_runs_the_callback_on_a_falsy_condition(): void
    {
        $shape = JsonShape::empty();

        $shape->unless(false, fn (JsonShape $shape) => $shape->merge(['examples.unlessPassed' => true]));

        $this->assertSame(['examples' => ['unlessPassed' => true]], $shape->toArray());
    }

    public function test_unless_skips_the_callback_on_a_truthy_condition(): void
    {
        $shape = JsonShape::empty();

        $shape->unless(true, fn (JsonShape $shape) => $shape->merge(['examples.unlessPassed' => true]));

        $this->assertSame([], $shape->toArray());
    }

    public function test_it_keeps_the_concrete_shape_type(): void
    {
        $shape = TraceShape::empty();

        $this->assertInstanceOf(TraceShape::class, $shape->when(true, fn () => null));
    }
}
