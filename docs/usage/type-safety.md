# Chapter 6 — Type safety in depth

> **The question:** what does static analysis actually catch for me?

This is the chapter that explains the package's main promise. Because a shape is
typed, the `@phpstan-type` block isn't just documentation — it drives static
analysis. Once a shape is described, its keys and value types are known
everywhere it's read or written.

::: tip Prerequisite
The guarantees below come from running [PHPStan](https://phpstan.org/) (or
[Larastan](https://github.com/larastan/larastan)). Without it you still get
runtime behaviour and IDE autocomplete — but the *enforcement* is what static
analysis adds. If you're not running it in CI yet, this chapter is the reason to
start.
:::

It uses the [`TraceShape`](/usage/defining-a-shape) from Chapter 1.

## How PHPStan reads the contract

Each line of the `@phpstan-type` block is a rule PHPStan enforces against every
`$this->attributes[...]` access in the class:

| Declaration | What it means | What PHPStan enforces |
| --- | --- | --- |
| `traceId: string` | required string | key must exist; value is a `string` |
| `level: value-of<LogLevelEnum>` | one of the enum's backing values | only `'alert' \| 'info' \| 'debug'` |
| `exception?: string` | optional | may be absent; narrow before use |
| `stackTrace: string\|null` | nullable | present, but may be `null` |
| `context: array{...}` | nested object | inner keys are typed too |
| `duration?: int` | optional int | may be absent |

## A misspelled key is caught

Reading a key that isn't in the shape fails analysis before the code ever runs —
the typo class of bug, eliminated:

```php
public string $spanId {
    get => $this->attributes['spamId']; // PHPStan: offsetAccess.notFound
}
```

## Nested keys are typed too

The inner `context` object is just as strict as the top level. Drill in and the
checker follows:

```php
$context = $shape->context;

$context['userId'];  // ok — string
$context['foobar'];  // PHPStan: offsetAccess.notFound
```

## Subset types flow through your methods

You can reference a *slice* of the shape in a docblock so a method's input and
output stay tied to the source definition — no second type to keep in sync.

On a getter, `@return` keeps the returned array typed as the nested `context`
object:

```php
/**
 * @return TraceJsonShape['context']
 */
public function getContext(): array
{
    return $this->attributes['context'];
}
```

On a setter, `@param` guarantees the structure passed in matches:

```php
/**
 * @param TraceJsonShape['context'] $value
 */
public function setContext(array $value): self
{
    return $this->tap(fn () => $this->attributes['context'] = $value);
}
```

Change the `context` definition in one place and both the getter and setter
re-check automatically. This is the mechanism every chapter quietly leans on.

## Enums via `value-of`

`level: value-of<LogLevelEnum>` ties the stored string to a backed enum. PHPStan
narrows it to exactly the enum's cases, so `LogLevelEnum::from($this->attributes['level'])`
is known to be safe — there's no unhandled string to worry about.

## Current limitations

Type safety is strong but not total. Know these edges so they don't surprise you:

- **Extra keys on a setter aren't rejected** — only wrong *types* on declared keys
  are. Set exactly the keys your shape declares
  ([Chapter 3](/usage/writing-values)).
- **The `fluent` helper bypasses key-existence checks** — it reads from a generic
  `Fluent`, so a typo'd key there isn't caught ([Chapter 2](/usage/reading-values)).
- **Typed writable hooks need a `get`-side ignore** — `@phpstan-ignore
  propertyGetHook.noRead` on the getter line ([Chapter 3](/usage/writing-values)).

These are sharp corners, not deal-breakers — and exactly the kind of thing
[contributions](https://github.com/plumthedev/json-shape) can help smooth out.

## Best practices

- **Run PHPStan/Larastan in CI** — the guarantees in this chapter only fire when
  analysis runs.
- **Edit the `@phpstan-type` block first** when the JSON changes; let analysis
  walk you to every accessor that needs updating.
- **Use subset types (`Shape['key']`)** instead of redeclaring nested structures.

---

**Previous:** [Chapter 5 — Create & combine shapes](/usage/creating-shapes) ·
**Next:** [Chapter 7 — Helpers, macros & errors →](/usage/helpers)
