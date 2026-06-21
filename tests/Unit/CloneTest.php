<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\SampleTrace;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class CloneTest extends TestCase
{
    public function test_it_returns_a_new_instance(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc-123']);

        $copy = $shape->clone();

        $this->assertNotSame($shape, $copy);
        $this->assertSame($shape->toArray(), $copy->toArray());
    }

    public function test_it_keeps_the_concrete_shape_type(): void
    {
        $shape = TraceShape::make(SampleTrace::payload());

        $this->assertInstanceOf(TraceShape::class, $shape->clone());
    }

    public function test_mutating_the_copy_does_not_affect_the_original(): void
    {
        $trace = TraceShape::make(SampleTrace::payload());

        $copy = $trace->clone();
        $copy->setDuration(777);

        $this->assertSame(777, $copy->getDuration());
        $this->assertSame(0, $trace->getDuration());
    }

    public function test_mutating_a_nested_value_on_the_copy_does_not_leak_into_the_original(): void
    {
        $trace = TraceShape::make(SampleTrace::payload());

        $copy = $trace->clone();
        $copy->setContext(['userId' => 'changed', 'levelNumber' => 99]);

        $this->assertSame('changed', $copy->getContext()['userId']);
        $this->assertSame('u-1', $trace->getContext()['userId']);
    }
}
