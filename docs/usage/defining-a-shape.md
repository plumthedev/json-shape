# Chapter 1 — Define a shape

> **The question:** how do I describe the structure of a JSON column so the rest
> of my code can rely on it?

A *shape* is a small class that gives a JSON structure a name, a home, and a
type. You write it once; from then on every read and write goes through it, and
your IDE and PHPStan know exactly what's inside.

## Why a class — not just an array?

A JSON column decoded into an `array` has no name and no contract. The structure
lives in your head and in a migration comment. JsonShape moves that knowledge
into one place — a class — so that:

- the structure has a **single source of truth** every part of your app can point at;
- PHPStan can **enforce** it instead of trusting you to remember the keys;
- your editor can **autocomplete** fields and methods.

That's the whole idea. Everything else in this guide is detail.

## The smallest possible shape

Extend `JsonShape`, declare the structure in a `@phpstan-type` block, and wire it
in with `@extends`:

```php
<?php

namespace App\Shapes;

use Plumthedev\JsonShape\JsonShape;

/**
 * @phpstan-type TraceJsonShape array{
 *     traceId: string,
 *     message: string,
 * }
 *
 * @extends JsonShape<TraceJsonShape>
 */
class TraceShape extends JsonShape
{
    public string $traceId {
        get => $this->attributes['traceId'];
    }
}
```

Two annotations are doing the work:

- **`@phpstan-type TraceJsonShape array{...}`** is the contract — the shape's
  single source of truth. It's a docblock, so it adds **zero runtime cost**; the
  structure lives entirely in PHPStan's type system, which is the right place for
  it since the data itself is dynamic JSON.
- **`@extends JsonShape<TraceJsonShape>`** plugs that contract into static
  analysis, so every `$this->attributes[...]` access inside the class is checked
  against it.

The `traceId` accessor is a [PHP 8.4 property hook](/usage/reading-values). It's
just a teaser here — Chapter 2 covers every way to expose a value.

## The full running example

The rest of the guide grows `TraceShape` into something realistic: required
fields, an optional one, a nullable one, an enum, and a nested object. Here's the
complete contract we'll build against:

```php
/**
 * @phpstan-type TraceJsonShape array{
 *     traceId: string,
 *     spanId: string,
 *     level: value-of<LogLevelEnum>,
 *     message: string,
 *     timestamp: string,
 *     exception?: string,
 *     stackTrace: string|null,
 *     context: array{
 *         userId: string,
 *         levelNumber: int,
 *     },
 *     duration?: int,
 * }
 *
 * @extends JsonShape<TraceJsonShape>
 */
class TraceShape extends JsonShape
{
    // accessors added over the next chapters…
}
```

Each field demonstrates one kind of declaration, and [Chapter 6](/usage/type-safety)
explains exactly what each one means to PHPStan. The `level` field is backed by a
plain PHP enum:

```php
<?php

namespace App\Shapes;

enum LogLevelEnum: string
{
    case ALERT = 'alert';
    case INFO = 'info';
    case DEBUG = 'debug';
}
```

## You don't `new` a shape

JsonShape's constructor is intentionally `protected` and `final`, so this won't
work:

```php
$trace = new TraceShape([...]); // ✗ won't compile
```

Shapes are created through factory methods (`make()`, `fromJson()`, `empty()`) or
— most often — by an [Eloquent cast](/usage/eloquent-casting) when a model loads.
This keeps construction consistent and gives the package one place to decode JSON.
You'll use both paths soon; for now just know that's the rule.

## Best practices

- **Name shapes `XxxShape`** and keep them in a dedicated namespace (`App\Shapes`)
  so they're easy to find and obviously not models or DTOs.
- **Treat the `@phpstan-type` block as the source of truth.** When the JSON
  structure changes, change it here first — analysis will then point you at every
  accessor that needs updating.
- **Don't add fields you never read.** A shape should describe the part of the
  JSON your app actually uses, not mirror every key for completeness.

---

**Next:** [Chapter 2 — Read values →](/usage/reading-values)
