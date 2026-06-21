<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\Exceptions\JsonShapeException;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;
use RuntimeException;

#[CoversClass(JsonShapeException::class)]
final class JsonShapeExceptionTest extends TestCase
{
    public function test_it_is_a_runtime_exception(): void
    {
        $this->assertInstanceOf(RuntimeException::class, new JsonShapeException('boom'));
    }

    public function test_missing_key_builds_a_descriptive_message(): void
    {
        $shape = TraceShape::make(['traceId' => 'abc-123']); // @phpstan-ignore argument.type

        $exception = JsonShapeException::missingKey('stackTrace', $shape);

        $this->assertSame(
            'Undefined key [stackTrace] for shape ['.TraceShape::class.']',
            $exception->getMessage(),
        );
    }

    public function test_missing_key_supports_an_integer_key(): void
    {
        $exception = JsonShapeException::missingKey(0, JsonShape::empty());

        $this->assertStringContainsString('Undefined key [0]', $exception->getMessage());
    }

    public function test_missing_key_captures_the_shape_as_context(): void
    {
        $shape = TraceShape::make(['traceId' => 'abc-123']); // @phpstan-ignore argument.type

        $exception = JsonShapeException::missingKey('stackTrace', $shape);

        $this->assertSame(['shape' => ['traceId' => 'abc-123']], $exception->getContext());
    }

    public function test_context_defaults_to_an_empty_array(): void
    {
        $this->assertSame([], (new JsonShapeException('boom'))->getContext());
    }

    public function test_explicit_context_is_preserved(): void
    {
        $exception = new JsonShapeException('boom', ['key' => 'value']);

        $this->assertSame(['key' => 'value'], $exception->getContext());
    }
}
