<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use Plumthedev\JsonShape\Casts\AsJsonShape;
use Plumthedev\JsonShape\JsonShape;
use Plumthedev\JsonShape\Tests\Fixtures\ExampleModel;
use Plumthedev\JsonShape\Tests\Fixtures\SampleTrace;
use Plumthedev\JsonShape\Tests\Fixtures\TraceShape;
use Plumthedev\JsonShape\Tests\TestbenchTestCase;

#[CoversClass(AsJsonShape::class)]
#[CoversClass(JsonShape::class)]
final class EloquentCastTest extends TestbenchTestCase
{
    private function fresh(mixed $id): ExampleModel
    {
        /** @var ExampleModel $model */
        $model = ExampleModel::query()->findOrFail($id);

        return $model;
    }

    public function test_the_migration_creates_the_examples_table(): void
    {
        $this->assertTrue(Schema::hasTable('examples'));
        $this->assertTrue(Schema::hasColumns('examples', ['trace', 'meta']));
    }

    public function test_a_json_column_is_read_back_as_a_typed_shape(): void
    {
        $id = ExampleModel::create(['trace' => SampleTrace::payload()])->getKey();

        $model = $this->fresh($id);

        $this->assertInstanceOf(TraceShape::class, $model->trace);
        $this->assertSame('abc-123', $model->trace->traceId);
        $this->assertSame('Request handled', $model->trace->getMessage());
    }

    public function test_the_column_is_stored_as_json_in_the_database(): void
    {
        $id = ExampleModel::create(['trace' => SampleTrace::payload()])->getKey();

        $raw = $this->fresh($id)->getRawOriginal('trace');

        $this->assertIsString($raw);
        $this->assertJson($raw);
        $this->assertSame(SampleTrace::payload(), json_decode($raw, true));
    }

    public function test_a_shape_instance_can_be_persisted_directly(): void
    {
        $shape = TraceShape::make(SampleTrace::payload());

        $id = ExampleModel::create(['trace' => $shape])->getKey();

        $this->assertSame('abc-123', $this->fresh($id)->trace->traceId);
    }

    public function test_mutations_round_trip_through_a_save(): void
    {
        $model = ExampleModel::create(['trace' => SampleTrace::payload()]);

        $model->trace->setContext(['userId' => 'e92fd2c9', 'levelNumber' => 16]);
        $model->trace = $model->trace;
        $model->save();

        $this->assertSame(
            ['userId' => 'e92fd2c9', 'levelNumber' => 16],
            $this->fresh($model->getKey())->trace->getContext(),
        );
    }

    public function test_a_generic_cast_returns_a_plain_json_shape(): void
    {
        $id = ExampleModel::create([
            'trace' => SampleTrace::payload(),
            'meta' => ['source' => 'unit-test'],
        ])->getKey();

        $meta = $this->fresh($id)->meta;

        $this->assertInstanceOf(JsonShape::class, $meta);
        $this->assertNotInstanceOf(TraceShape::class, $meta);
        $this->assertSame(['source' => 'unit-test'], $meta->toArray());
    }

    public function test_a_null_column_casts_to_null(): void
    {
        $id = ExampleModel::create([
            'trace' => SampleTrace::payload(),
            'meta' => null,
        ])->getKey();

        $this->assertNull($this->fresh($id)->meta);
    }
}
