# Chapter 7 — Helpers, macros & errors

> **The question:** what else does a shape give me out of the box?

A shape is meant to feel like a first-class Laravel citizen, so it pulls in the
framework traits you already use and ships a small exception for missing data.
This final chapter is a tour of the extras.

Everything builds on the [`TraceShape`](/usage/defining-a-shape) from Chapter 1.

## Conditional calls

From `Conditionable`, `when()` and `unless()` let you branch a chain without
breaking it:

```php
$shape->when($isRetry, fn (TraceShape $shape) => $shape->merge(['context.retry' => true]));
$shape->unless($isRetry, fn (TraceShape $shape) => $shape->merge(['context.retry' => false]));
```

## Side effects with `tap`

From `Tappable`, `tap()` runs a side effect and returns the shape — the same
helper the setters in [Chapter 3](/usage/writing-values) are built on:

```php
$shape->tap(fn (TraceShape $shape) => Log::info('trace', $shape->toArray()));
```

## Extending a shape with macros

From `Macroable`, `macro()` adds methods to a shape at runtime — perfect for
domain predicates you don't want to bake into the class:

```php
TraceShape::macro('isDebug', fn () => $this->level === LogLevelEnum::DEBUG);
TraceShape::macro('isSlowRequest', fn (int $thresholdMs = 1000) => $this->getDuration() > $thresholdMs);

$shape->isDebug();
$shape->isSlowRequest(2000);
```

Register macros in a service provider's `boot()` method so they're available
everywhere.

## Dumping while you debug

From `Dumpable`, `dump()` and `dd()` work inline, mid-chain:

```php
$shape->dump(); // print and continue
$shape->dd();   // print and halt
```

## Error handling

When a required key is missing, throw a `JsonShapeException` via `missingKey()`.
It captures the shape as context, so a failure tells you *which* data was wrong —
not just that something was:

```php
use Plumthedev\JsonShape\Exceptions\JsonShapeException;

throw JsonShapeException::missingKey('stackTrace', $this);
```

The attached context is available on the caught exception through
`getContext()`, which returns the shape as an array:

```php
try {
    $shape->getStackTrace();
} catch (JsonShapeException $e) {
    report($e->getContext()); // ['shape' => [...the full shape as an array...]]
}
```

Use this for the strict accessor pattern from
[Chapter 2](/usage/reading-values#handling-optional-and-nullable-values): `try*`
methods return `null`, `get*` methods throw `missingKey()`.

## Best practices

- **Reach for the trait helpers** before writing your own — `when`, `tap`, and
  friends keep shape code fluent and idiomatic.
- **Put domain predicates in macros** (registered in a provider) to keep shapes
  focused on structure.
- **Throw `missingKey()` from strict getters** so failures carry the data that
  caused them.

---

## You've finished the guide 🎉

You now know every part of JsonShape — defining a shape, reading and writing it,
casting it on a model, building it by hand, the type-safety guarantees, and the
helpers. From here:

- Revisit the [Overview](/usage) to jump back to any chapter.
- Star or watch the project on [GitHub](https://github.com/plumthedev/json-shape).
- Hit a rough edge or have an idea? [Open an issue or PR](https://github.com/plumthedev/json-shape/issues) —
  the conventions are still being shaped, and input is welcome.

**Previous:** [Chapter 6 — Type safety in depth](/usage/type-safety)
