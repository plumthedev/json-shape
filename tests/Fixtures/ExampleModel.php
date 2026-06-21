<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Plumthedev\JsonShape\Casts\AsJsonShape;
use Plumthedev\JsonShape\JsonShape;

/**
 * @property TraceShape $trace
 * @property JsonShape<array<string, mixed>>|null $meta
 */
class ExampleModel extends Model
{
    protected $table = 'examples';

    public $timestamps = true;

    /** @var list<string> */
    protected $fillable = ['trace', 'meta'];

    /** @return array<string, mixed> */
    public function casts(): array
    {
        return [
            // A dedicated, typed shape class.
            'trace' => AsJsonShape::of(TraceShape::class),
            // A generic, untyped shape.
            'meta' => AsJsonShape::class,
        ];
    }
}
