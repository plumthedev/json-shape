<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\Casts\AsJsonShape;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\SampleTrace;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestCase;

#[CoversClass(AsJsonShape::class)]
final class AsJsonShapeTest extends TestCase
{
    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends Model {};
    }

    public function test_of_builds_the_cast_definition_string(): void
    {
        $this->assertSame(
            AsJsonShape::class.':'.TraceShape::class,
            AsJsonShape::of(TraceShape::class),
        );
    }

    public function test_get_decodes_json_into_the_configured_shape(): void
    {
        $cast = AsJsonShape::castUsing([TraceShape::class]);
        $attributes = ['trace' => json_encode(SampleTrace::payload())];

        $shape = $cast->get($this->model, 'trace', $attributes['trace'], $attributes);

        $this->assertInstanceOf(TraceShape::class, $shape);
        $this->assertSame('abc-123', $shape->traceId);
    }

    public function test_get_falls_back_to_a_generic_shape_without_a_class(): void
    {
        $cast = AsJsonShape::castUsing([]);
        $attributes = ['meta' => json_encode(['foo' => 'bar'])];

        $shape = $cast->get($this->model, 'meta', $attributes['meta'], $attributes);

        $this->assertInstanceOf(JsonShape::class, $shape);
        $this->assertNotInstanceOf(TraceShape::class, $shape);
        $this->assertSame(['foo' => 'bar'], $shape->toArray());
    }

    public function test_get_returns_null_when_the_value_is_not_an_array(): void
    {
        $cast = AsJsonShape::castUsing([TraceShape::class]);
        $attributes = ['trace' => json_encode('a plain string')];

        $shape = $cast->get($this->model, 'trace', $attributes['trace'], $attributes);

        $this->assertNull($shape);
    }

    public function test_set_encodes_a_shape_to_json(): void
    {
        $cast = AsJsonShape::castUsing([TraceShape::class]);
        $shape = TraceShape::make(SampleTrace::payload());

        $result = $cast->set($this->model, 'trace', $shape, []);

        $this->assertSame(['trace' => json_encode(SampleTrace::payload())], $result);
    }

    public function test_set_encodes_a_plain_array_to_json(): void
    {
        $cast = AsJsonShape::castUsing([TraceShape::class]);

        // A plain array is encoded too — the cast accepts any JSON-serializable value.
        $result = $cast->set($this->model, 'trace', ['traceId' => 'abc'], []); // @phpstan-ignore argument.type

        $this->assertSame(['trace' => '{"traceId":"abc"}'], $result);
    }

    public function test_set_keeps_null_as_null(): void
    {
        $cast = AsJsonShape::castUsing([TraceShape::class]);

        $result = $cast->set($this->model, 'trace', null, []);

        $this->assertSame(['trace' => null], $result);
    }
}
