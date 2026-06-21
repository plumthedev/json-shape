<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\SampleTrace;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class SettingPropertiesTest extends TestCase
{
    private function trace(): TraceShape
    {
        return TraceShape::make(SampleTrace::payload());
    }

    public function test_a_standard_setter_mutates_the_attributes(): void
    {
        $trace = $this->trace();

        $trace->setDuration(504);

        $this->assertSame(504, $trace->getDuration());
    }

    public function test_a_standard_setter_returns_the_same_instance(): void
    {
        $trace = $this->trace();

        $this->assertSame($trace, $trace->setDuration(504));
    }

    public function test_setters_are_chainable(): void
    {
        $trace = $this->trace();

        $trace
            ->setDuration(504)
            ->setContext(['userId' => 'e92fd2c9', 'levelNumber' => 16]);

        $this->assertSame(504, $trace->getDuration());
        $this->assertSame(['userId' => 'e92fd2c9', 'levelNumber' => 16], $trace->getContext());
    }

    public function test_a_writable_property_hook_sets_a_value(): void
    {
        $trace = $this->trace();

        $trace->context = ['userId' => 'user-id', 'levelNumber' => 16];

        $this->assertSame(['userId' => 'user-id', 'levelNumber' => 16], $trace->context);
    }

    public function test_a_writable_property_hook_reads_a_value(): void
    {
        $trace = $this->trace();

        $this->assertSame(['userId' => 'u-1', 'levelNumber' => 1], $trace->context);
    }

    public function test_array_access_writes_a_value(): void
    {
        $shape = JsonShape::empty();

        $shape['traceId'] = 'abc';

        $this->assertSame('abc', $shape['traceId']);
    }

    public function test_array_access_removes_a_value(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc']);

        unset($shape['traceId']);

        $this->assertFalse(isset($shape['traceId']));
    }
}
