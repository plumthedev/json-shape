---
# https://vitepress.dev/reference/default-theme-home-page
layout: home

hero:
  name: "JsonShape"
  text: "Give JSON a shape"
  tagline: "Typed objects for your database JSON columns."
  actions:
    - theme: brand
      text: Get Started
      link: "#quick-start"
    - theme: alt
      text: View on GitHub
      link: https://github.com/plumthedev/json-shape

features:
  - title: Typed by design
    details: "Model a JSON structure once and read its values as typed properties, with full IDE autocomplete and static analysis."
  - title: Eloquent casting
    details: "Cast a JSON column straight to a shape — decoded on read, encoded on save, no accessors or manual JSON handling."
  - title: Familiar to use
    details: "Works like the Laravel you know: array access, fluent reads, conditionals, macros and dumping all come built in."
  - title: Easy to compose
    details: "Build shapes from arrays or JSON, merge them with dot notation, and clone safely without touching the original."
---

## The problem

A JSON column comes back from Eloquent as a plain `array` — no autocomplete,
no static analysis, and a typo only blows up at runtime. **JsonShape** turns
that array into a typed object you define once, while staying a thin wrapper
you can still treat like an array.

## Quick start

Install in via Composer, requires PHP **8.4+** and **Laravel 13**.

```bash
composer require plumthedev/json-shape
```

::: info
Support for older PHP and Laravel versions is on the way.
:::

### Describe the shape

Extend `JsonShape` and expose typed accessors. Property hooks read straight from
the underlying `$attributes`, and the `@phpstan-type` / `@extends` annotations
make the whole thing type-safe under static analysis.

```php
<?php

namespace App\Shapes;

use Plumthedev\JsonShape\JsonShape;

/**
 * @phpstan-type TraceJsonShape array{
 *     traceId: string,
 *     message: string,
 *     duration?: int,
 *     context: array{
 *         userId: string,
 *         levelNumber: int,
 *     },
 * }
 *
 * @extends JsonShape<TraceJsonShape>
 */
class TraceShape extends JsonShape
{
    // Typed read access via a property hook.
    public string $traceId {
        get => $this->attributes['traceId'];
    }

    // A plain typed getter works just as well.
    public function getMessage(): string
    {
        return $this->attributes['message'];
    }

    // Lean on Laravel's Fluent for coercion and defaults.
    public function getDuration(): int
    {
        return $this->fluent->integer('duration');
    }

    // Fluent setter: tap() mutates and returns $this for chaining.
    public function setContext(array $value): self
    {
        return $this->tap(fn () => $this->attributes['context'] = $value);
    }
}
```

### Cast it on the model

Point an Eloquent cast at your shape and the JSON column is decoded into a
`TraceShape` on read and encoded back to JSON on save.

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

::: info
Need a generic, untyped shape instead of a dedicated class? Cast with `AsJsonShape::class` and you'll get a plain `JsonShape` back.
:::

### Use it like an object

```php
$example = Example::find(1);

// Read with autocomplete and types.
$example->trace->traceId;          // string
$example->trace->getDuration();    // int

// Write through your setters — chainable.
$example->trace
    ->setContext(['userId' => 'e92fd2c9', 'levelNumber' => 16]);

$example->save();
```

You can also build shapes by hand, combine them, or copy them without touching
the original:

```php
use App\Shapes\TraceShape;

$trace = TraceShape::make([
    'traceId' => 'abc-123',
    'message' => 'Request handled',
    'context' => ['userId' => 'u-1', 'levelNumber' => 1],
]);

$trace = TraceShape::fromJson($jsonString); // from a raw JSON string
$trace = TraceShape::empty();               // a blank shape

$trace->merge(['context.userId' => 'u-2']); // dot-notation merge
$copy = $trace->clone();                    // independent deep copy
```
