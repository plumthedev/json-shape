<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\Exceptions\JsonShapeException;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\LogLevelEnum;
use Plumthedev\JsonShape\Tests\Fixtures\SampleTrace;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class ReadingPropertiesTest extends TestCase
{
    /** @param array<string, mixed> $overrides */
    private function trace(array $overrides = []): TraceShape
    {
        // @phpstan-ignore argument.type
        return TraceShape::make(array_merge(SampleTrace::payload(), $overrides));
    }

    public function test_a_property_hook_reads_straight_from_attributes(): void
    {
        $this->assertSame('abc-123', $this->trace()->traceId);
    }

    public function test_a_property_hook_can_transform_on_the_way_out(): void
    {
        $this->assertSame(LogLevelEnum::DEBUG, $this->trace(['level' => 'debug'])->level);
        $this->assertSame(LogLevelEnum::ALERT, $this->trace(['level' => 'alert'])->level);
    }

    public function test_a_standard_getter_returns_a_value(): void
    {
        $this->assertSame('Request handled', $this->trace()->getMessage());
    }

    public function test_a_getter_can_build_an_object(): void
    {
        $timestamp = $this->trace(['timestamp' => '2026-06-21 12:00:00'])->getTimestamp();

        $this->assertInstanceOf(DateTimeInterface::class, $timestamp);
        $this->assertSame('2026-06-21 12:00:00', $timestamp->format('Y-m-d H:i:s'));
    }

    public function test_a_nested_structure_is_returned_as_is(): void
    {
        $this->assertSame(['userId' => 'u-1', 'levelNumber' => 1], $this->trace()->getContext());
    }

    public function test_try_pattern_returns_null_when_the_key_is_absent(): void
    {
        $this->assertNull($this->trace()->tryException());
    }

    public function test_try_pattern_returns_the_value_when_present(): void
    {
        $this->assertSame('boom', $this->trace(['exception' => 'boom'])->tryException());
    }

    public function test_a_required_getter_returns_the_value_when_present(): void
    {
        $this->assertSame('the-stack', $this->trace(['stackTrace' => 'the-stack'])->getStackTrace());
    }

    public function test_a_required_getter_throws_when_the_key_is_missing(): void
    {
        $this->expectException(JsonShapeException::class);
        $this->expectExceptionMessage('Undefined key [stackTrace]');

        $this->trace(['stackTrace' => null])->getStackTrace();
    }

    public function test_fluent_coerces_a_value(): void
    {
        $this->assertSame(504, $this->trace(['duration' => '504'])->getDuration());
    }

    public function test_fluent_returns_the_default_for_a_missing_key(): void
    {
        $this->assertSame(0, $this->trace()->getDuration());
    }
}
