<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\TestCase;
use Symfony\Component\VarDumper\VarDumper;

#[CoversClass(JsonShape::class)]
final class DumpableTest extends TestCase
{
    /** @var list<mixed> */
    private array $dumped = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Capture dumps instead of letting them leak to stdout.
        $this->dumped = [];
        VarDumper::setHandler(function (mixed $value): void {
            $this->dumped[] = $value;
        });
    }

    protected function tearDown(): void
    {
        VarDumper::setHandler(null);

        parent::tearDown();
    }

    public function test_dump_returns_the_shape_for_chaining(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc']);

        $returned = $shape->dump();

        $this->assertSame($shape, $returned);
    }

    public function test_dump_passes_the_shape_to_the_dumper(): void
    {
        $shape = JsonShape::make(['traceId' => 'abc']);

        $shape->dump();

        $this->assertContains($shape, $this->dumped);
    }
}
