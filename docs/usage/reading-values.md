# Chapter 2 — Read values

> **The question:** how do I get a typed value out of a shape?

A shape exposes the underlying JSON through accessors *you* write. There's no
hidden mapping layer to fight — you decide exactly how each value is read,
validated, or transformed. This chapter covers the four ways to read, from the
one you'll reach for most to the one you'll reach for least.

Everything builds on the [`TraceShape`](/usage/defining-a-shape) from Chapter 1.

## 1. Property hooks — the default

A [PHP 8.4 property hook](https://www.php.net/manual/en/language.oop5.property-hooks.php)
reads a value straight from `$this->attributes` and exposes it as a typed
property:

```php
public string $traceId {
    get => $this->attributes['traceId'];
}
```

Calling code reads it like any property, with full autocomplete and type info:

```php
$shape->traceId; // string
```

This is the cleanest option and the one to default to. Reach for the others when
you need logic, coercion, or method semantics.

### Transforming on the way out

A hook can return *anything* — so this is also where you turn raw JSON into rich
types. Cast a string to an enum, or build a value object:

```php
public LogLevelEnum $level {
    get => LogLevelEnum::from($this->attributes['level']);
}
```

This is a big part of the payoff: callers work with a `LogLevelEnum`, never the
raw `'debug'` string from the database.

## 2. Standard getters — when you want a method

If you prefer methods, or the read needs real logic, a plain getter works just as
well:

```php
public function getMessage(): string
{
    return $this->attributes['message'];
}
```

Use a getter (over a hook) when the access takes arguments, throws, or is
genuinely a behaviour rather than a field — for example building an object:

```php
public function getTimestamp(): DateTimeInterface
{
    return new DateTime($this->attributes['timestamp']);
}
```

## 3. The `fluent` helper — for coercion

Every shape exposes a Laravel
[`Fluent`](https://laravel.com/api/master/Illuminate/Support/Fluent.html)
instance via `$this->fluent`, which is handy for type coercion and quick
collection work:

```php
public function getDuration(): int
{
    return $this->fluent->integer('duration'); // coerces, defaults to 0 if absent
}
```

```php
$this->fluent->integer('duration');
$this->fluent->string('message')->length();
$this->fluent->collect('context')->count();
```

::: warning Pitfall
Reading through `fluent` **bypasses the static key-existence check** you get from
typed `$this->attributes[...]` access. PHPStan won't catch a typo'd key here. Use
it deliberately, for coercion — not as your default reader.
:::

## 4. Array access — for ad-hoc reads

A shape implements `ArrayAccess`, so when you just want a value without writing an
accessor, treat it like the array it wraps:

```php
$shape['traceId'];        // read
isset($shape['traceId']); // exists
```

This is an escape hatch, useful in tests or one-off scripts. In application code,
a named accessor reads better and stays type-checked.

## Handling optional and nullable values

You're in full control of how missing data is handled, which means you get to
make the intent explicit. Two patterns cover almost everything.

**Soft access — return `null` when a key may be absent:**

```php
public function tryException(): ?string
{
    return $this->attributes['exception'] ?? null;
}
```

**Strict access — require the key and fail loudly if it's gone:**

```php
public function getStackTrace(): string
{
    $trace = $this->attributes['stackTrace'] ?? null;

    if ($trace) {
        return $trace;
    }

    throw JsonShapeException::missingKey('stackTrace', $this);
}
```

A naming convention keeps the two obvious at the call site: prefix soft readers
with `try`, strict ones with `get`.

```php
$exception = $shape->tryException();

if ($exception === null) {
    return null;
}

return ['exception' => $exception, 'stacktrace' => $shape->getStackTrace()];
```

::: tip
`JsonShapeException::missingKey()` attaches the shape as context, so failures are
easy to trace. See [Chapter 7](/usage/helpers#error-handling) for handling it.
:::

## Best practices

- **Default to property hooks**; use getters for behaviour, `fluent` for
  coercion, array access for throwaway reads.
- **Decide null vs. throw on purpose** and signal it with the `try` / `get`
  naming convention.
- **Keep `fluent` for coercion**, not as a way around the type checker.

---

**Previous:** [Chapter 1 — Define a shape](/usage/defining-a-shape) ·
**Next:** [Chapter 3 — Write values →](/usage/writing-values)
