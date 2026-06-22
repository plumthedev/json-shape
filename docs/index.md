---
# https://vitepress.dev/reference/default-theme-home-page
layout: home

hero:
  name: "JsonShape"
  text: "Your JSON columns deserve types"
  tagline: "Describe a JSON column once, and Eloquent hands you a typed object — autocomplete, static analysis, and methods — without giving up the array you already know."
  actions:
    - theme: brand
      text: Get Started
      link: "#quick-start"
    - theme: alt
      text: Why JsonShape?
      link: "#the-2am-problem"
    - theme: alt
      text: View on GitHub
      link: https://github.com/plumthedev/json-shape

features:
  - title: Catch JSON bugs in CI, not production
    details: "A misspelled or missing key fails PHPStan/Larastan instead of paging you at 2am. The shape's @phpstan-type block is the single source of truth."
  - title: One cast, then forget about it
    details: "Point an Eloquent cast at your shape — decoded on read, encoded on save. No accessors, no mutators, no json_decode scattered across your models."
  - title: Your IDE finally gets it
    details: "Autocomplete, jump-to-definition, and inline types on every field, because each shape is a real class — not an array you have to remember the keys of."
  - title: Still an array when you want one
    details: "Array access, Fluent coercion, conditionals, macros, and dump()/dd() all work out of the box. Build, merge with dot notation, and clone shapes safely."
---

## The 2am problem

You reach for a JSON column because the data is structured but doesn't deserve
its own table — user preferences, a cached API response, audit context, a
feature-flag payload. It works. Then six months later:

```php
$user->preferences['notifcations']['email']; // typo. ships green. pages you at 2am.
```

Eloquent hands JSON columns back as a plain `array` — so **no autocomplete, no
static analysis, no guarantees**. The structure lives only in your head and in a
migration comment nobody reads. Every access is a leap of faith, and the answer
arrives in production as an `undefined array key` deep inside a controller.

JsonShape turns that array into a **typed object you define once**, while staying
a thin wrapper you can still treat like an array.

```php
$user->preferences->theme;          // string — autocompleted, type-checked
$user->preferences->emailEnabled(); // bool — and that 2am typo? now a PHPStan error
```

Same column. Same database. Your editor and your CI now catch the mistakes you
used to ship.

::: tip Who is this for?
Laravel developers who store structured data in JSON columns — settings,
metadata, cached API payloads, audit context, feature flags, traces — and who
run PHPStan/Larastan and want that data held to the same standard as the rest of
their typed codebase.
:::

## "Why not just use an array?"

The honest answers to the questions developers ask before adopting:

- **"It's just an array cast, why add a class?"** An array cast gives you data
  with zero guarantees. The moment that JSON is read in more than one place, its
  structure becomes tribal knowledge. A shape makes it a single, type-checked
  source of truth — and PHPStan enforces it for free.
- **"Can't I write accessors for this?"** You can, and you end up with mutators,
  `json_decode`, and `data_get()` scattered across the model. JsonShape is one
  cast and one class; reads and writes flow through it automatically.
- **"Isn't a full DTO/validation library better?"** Sometimes — if you need
  request validation, transformers, and mapping, reach for one. JsonShape is
  deliberately smaller: a thin wrapper that *stays an array* while adding types.
  No mapping layer, no hidden state. It does one thing.
- **"Will it slow my model down?"** It's a value object over the same decoded
  array Eloquent already produces. No reflection, no magic.

## Where teams use it

JsonShape pays off anywhere a JSON column has a knowable shape read in more than
one place:

- **User / tenant settings** — `preferences`, notification settings, theme and locale.
- **Cached or denormalized API payloads** — a typed view over a third-party response.
- **Audit & activity context** — structured metadata attached to log or event rows.
- **Feature-flag / experiment payloads** — typed access to rollout configuration.
- **Structured domain blobs** — order snapshots, pricing breakdowns, traces.

## Quick start

Install via Composer. Requires PHP **8.4+** and **Laravel 13**.

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

## When to use it — and when not to

JsonShape is the right tool for **typed Laravel JSON columns**, not every JSON
problem.

**Reach for it when:**

- A JSON column has a known, fairly stable structure you read in more than one place.
- You run PHPStan/Larastan and want your JSON access verified like the rest of your code.
- You'd otherwise be writing accessors or scattering `json_decode` across your models.

**Skip it when:**

- The data is genuinely schemaless or wildly varied — a plain `array`/`collection` cast is simpler.
- You need a relational schema with constraints and indexes — that's a table, not a JSON column.
- You only ever touch a single field once — the typing overhead isn't worth it.

We'd rather you not install it than fight it — if your case is in the "skip"
column, that's a good outcome too.

## An opinionated, community-shaped effort

Laravel gave us first-class casts, property hooks, and a strong static-analysis
story with Larastan. Typed JSON columns are the obvious next step — and there's
no agreed-on, ergonomic way to do it yet. JsonShape is an opinionated attempt to
define that pattern: types PHPStan enforces, an object that still feels like the
array underneath, and as little machinery as possible in between.

It's early, and the conventions are still being shaped — which is exactly why
input is welcome. Found a bug, or have a strong opinion about how typed JSON in
Laravel *should* feel? Open an
[issue](https://github.com/plumthedev/json-shape/issues) or a pull request on
[GitHub](https://github.com/plumthedev/json-shape).

## Keep reading

The [usage guide](/usage) is a set of short chapters that walk through one
running example end to end:

1. [Define a shape](/usage/defining-a-shape) — describe the structure of a JSON column
2. [Read values](/usage/reading-values) — get typed values out of a shape
3. [Write values](/usage/writing-values) — change values and keep them typed
4. [Cast it on a model](/usage/eloquent-casting) — use a shape as an Eloquent attribute
5. [Create & combine shapes](/usage/creating-shapes) — build shapes by hand, in tests and jobs
6. [Type safety in depth](/usage/type-safety) — what static analysis catches for you
7. [Helpers, macros & errors](/usage/helpers) — the extras a shape ships with
