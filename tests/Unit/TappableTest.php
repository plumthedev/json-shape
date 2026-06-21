<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class TappableTest extends TestCase
{
    public function test_tap_runs_the_side_effect_and_returns_the_shape(): void
    {
        $shape = JsonShape::empty();

        $returned = $shape->tap(fn (JsonShape $shape) => $shape->merge(['examples.tapped' => true]));

        $this->assertSame($shape, $returned);
        $this->assertSame(['examples' => ['tapped' => true]], $shape->toArray());
    }

    public function test_tap_without_a_callback_returns_a_higher_order_proxy(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc']);

        $returned = $shape->tap()->merge(['message' => 'hi']);

        $this->assertSame($shape, $returned);
        $this->assertSame(['traceId' => 'abc', 'message' => 'hi'], $shape->toArray());
    }
}
