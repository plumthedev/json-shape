<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class JsonShapeTest extends TestCase
{
    public function test_it_makes_a_shape_from_attributes(): void
    {
        $shape = JsonShape::make(['name' => 'json-shape']);

        $this->assertSame(['name' => 'json-shape'], $shape->toArray());
    }
}
