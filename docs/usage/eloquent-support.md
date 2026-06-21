# Eloquent support

JsonShape's main purpose is to make JSON columns first-class on your Eloquent
models. This page goes end to end: the migration, the cast, and using the shape.

It uses the [`TraceShape`](/usage/the-example-shape) defined earlier.

## 1. The migration

Store the data in a `json` column like any other JSON attribute:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examples', function (Blueprint $table) {
            $table->id();
            $table->json('trace');
            $table->timestamps();
        });
    }
};
```

## 2. The cast

Point an `AsJsonShape` cast at your shape. On read, the column's JSON is decoded
into a `TraceShape`; on save, the shape is encoded back to JSON. No accessors,
no manual `json_decode`.

```php
<?php

namespace App\Models;

use App\Shapes\TraceShape;
use Illuminate\Database\Eloquent\Model;
use Plumthedev\JsonShape\Casts\AsJsonShape;

/**
 * @property TraceShape $trace
 */
class Example extends Model
{
    protected $fillable = ['trace'];

    /** @return array<string, mixed> */
    public function casts(): array
    {
        return [
            'trace' => AsJsonShape::of(TraceShape::class),
        ];
    }
}
```

::: tip
Add the `@property TraceShape $trace` docblock on the model so your IDE and
PHPStan know the attribute's type.
:::

::: info
Need a generic, untyped shape instead of a dedicated class? Cast with
`AsJsonShape::class` and you'll get a plain `JsonShape` back.
:::

## 3. Using it

Once cast, the shape is just an attribute on the model — read and mutate it with
the accessors from [Reading](/usage/reading-properties) and
[Setting](/usage/setting-properties) properties:

```php
$model = Example::find(1);

// read
$model->trace->traceId;

// write — then persist as usual
$model->trace->setContext([
    'userId'      => 'superSimple',
    'levelNumber' => $model->trace->getNonsenseInt(),
]);

$model->save();
```

## Next

- [Common tools](/usage/common-tools)
