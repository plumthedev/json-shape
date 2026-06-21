<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use ArrayAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\Concerns\HasArrayAccess;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(HasArrayAccess::class)]
#[CoversClass(JsonShape::class)]
final class ArrayAccessTest extends TestCase
{
    public function test_a_shape_is_array_accessible(): void
    {
        $this->assertInstanceOf(ArrayAccess::class, JsonShape::empty());
    }

    public function test_offset_get_reads_a_value(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc-123']);

        $this->assertSame('abc-123', $shape['traceId']);
    }

    public function test_offset_exists_reports_presence(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc-123']);

        $this->assertTrue(isset($shape['traceId']));
        $this->assertFalse(isset($shape['missing']));
    }

    public function test_offset_exists_is_false_for_a_null_value(): void
    {
        $shape = JsonShape::make(['stackTrace' => null]);

        $this->assertFalse(isset($shape['stackTrace']));
    }

    public function test_offset_set_writes_a_value(): void
    {
        $shape = JsonShape::empty();

        $shape['traceId'] = 'abc';

        $this->assertSame('abc', $shape['traceId']);
        $this->assertSame(['traceId' => 'abc'], $shape->toArray());
    }

    public function test_offset_unset_removes_a_value(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc', 'message' => 'hi']);

        unset($shape['traceId']);

        $this->assertFalse(isset($shape['traceId']));
        $this->assertSame(['message' => 'hi'], $shape->toArray());
    }
}
