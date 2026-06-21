<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\LogLevelEnum;
use Plumthedev\JsonShape\Tests\Fixtures\SampleTrace;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(JsonShape::class)]
final class MacroableTest extends TestCase
{
    protected function tearDown(): void
    {
        TraceShape::flushMacros();

        parent::tearDown();
    }

    /** @param array<string, mixed> $overrides */
    private function trace(array $overrides = []): TraceShape
    {
        // @phpstan-ignore argument.type
        return TraceShape::make(array_merge(SampleTrace::payload(), $overrides));
    }

    public function test_a_macro_extends_the_shape_with_a_custom_method(): void
    {
        TraceShape::macro('isDebug', fn () => $this->level === LogLevelEnum::DEBUG);

        $debug = $this->trace(['level' => 'debug']);
        $info = $this->trace(['level' => 'info']);

        $this->assertTrue($debug->isDebug()); // @phpstan-ignore method.notFound
        $this->assertFalse($info->isDebug()); // @phpstan-ignore method.notFound
    }

    public function test_a_macro_can_accept_arguments(): void
    {
        TraceShape::macro('isSlowRequest', fn (int $thresholdMs = 1000) => $this->getDuration() > $thresholdMs);

        $shape = $this->trace(['duration' => 1500]);

        $this->assertTrue($shape->isSlowRequest()); // @phpstan-ignore method.notFound
        $this->assertFalse($shape->isSlowRequest(2000)); // @phpstan-ignore method.notFound
    }

    public function test_macro_existence_can_be_checked(): void
    {
        $this->assertFalse(TraceShape::hasMacro('isDebug'));

        TraceShape::macro('isDebug', fn () => true);

        $this->assertTrue(TraceShape::hasMacro('isDebug'));
    }
}
