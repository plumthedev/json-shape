# Chapter 4 — Cast it on a model

> **The question:** how do I use a shape as an attribute on an Eloquent model?

This is what JsonShape is for. With one cast, a JSON column is decoded into your
shape on read and encoded back to JSON on save — no accessors, no mutators, no
manual `json_decode`. This chapter goes end to end: migration, cast, usage.

It uses the [`TraceShape`](/usage/defining-a-shape) from Chapter 1.

## 1. The migration

Store the data in a plain `json` column, exactly as you would for any JSON
attribute:

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

JsonShape doesn't change your schema — it changes how PHP sees the column.

## 2. The cast

Point an `AsJsonShape` cast at your shape using `AsJsonShape::of(...)`. On read,
the column's JSON is decoded into a `TraceShape`; on save, the shape is encoded
back to JSON.

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

::: tip Best practice
Add the `@property TraceShape $trace` docblock on the model. The cast handles
the runtime conversion; this line tells your IDE and PHPStan the attribute's
type, so autocomplete and analysis work on `$model->trace`.
:::

::: info Need an untyped shape?
Cast with `AsJsonShape::class` (no `::of(...)`) and the column decodes into a
plain `JsonShape` — still array-accessible and serializable, just without a
dedicated class. Useful for genuinely loosely-structured columns.
:::

## 3. Using it

Once cast, the shape is just an attribute. Read it with the accessors from
[Chapter 2](/usage/reading-values), mutate it with the setters from
[Chapter 3](/usage/writing-values), then persist with `save()`:

```php
$model = Example::find(1);

// read — typed, autocompleted
$model->trace->traceId;
$model->trace->getDuration();

// write — mutate the shape, then persist as usual
$model->trace->setContext([
    'userId'      => 'e92fd2c9',
    'levelNumber' => 16,
]);

$model->save();
```

You can also assign a brand-new shape to the attribute — it's encoded to JSON on
save just the same:

```php
$model->trace = TraceShape::make([
    'traceId' => 'abc-123',
    'message' => 'Request handled',
    'context' => ['userId' => 'u-1', 'levelNumber' => 1],
]);

$model->save();
```

::: warning Pitfall
Mutating the shape changes the in-memory object only. **Nothing is written to the
database until you call `$model->save()`** — the same rule as any other Eloquent
attribute.
:::

## What about `null`?

If the column is `null` (or holds non-array JSON), the attribute reads back as
`null` rather than an empty shape. Assigning `null` and saving writes `null`
back. Guard with a null check, or seed a default with
[`TraceShape::empty()`](/usage/creating-shapes) when you create the row.

## Best practices

- **Always pair the cast with a `@property` docblock** so tooling sees the type.
- **Build new shapes with the [factory methods](/usage/creating-shapes)** rather
  than raw arrays when you want the value typed before it's assigned.
- **Remember `save()`** — mutating a shape is not persistence.

---

**Previous:** [Chapter 3 — Write values](/usage/writing-values) ·
**Next:** [Chapter 5 — Create & combine shapes →](/usage/creating-shapes)
