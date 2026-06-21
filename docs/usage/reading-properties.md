# Reading properties

A shape exposes the underlying JSON through accessors you control. There's no
magic mapping to fight — you decide exactly how each value is read, validated,
or transformed. This page covers the four ways to read a value: property hooks,
standard getters, the `fluent` helper, and array access.

All examples build on the [`TraceShape`](/usage/the-example-shape) defined
earlier.

## Property hooks

The cleanest way to expose a value is a PHP 8.4 property hook that returns it
straight from `$this->attributes`:

```php
public string $traceId {
    get => $this->attributes['traceId'];
}
```

Consuming code then reads it like any typed property, with full autocomplete:

```php
public function usingSimpleProperty(TraceShape $shape): string
{
    return $shape->traceId;
}
```

### Transforming on the way out

A hook can return anything — cast to an enum, build an object, derive a value:

```php
public LogLevelEnum $level {
    get => LogLevelEnum::from($this->attributes['level']);
}
```

```php
public function getTimestamp(): DateTimeInterface
{
    return new DateTime($this->attributes['timestamp']);
}
```

## Standard getters

If you prefer methods, regular getters work just as well:

```php
public function getMessage(): string
{
    return $this->attributes['message'];
}
```

### Nested structures

Return a nested object as-is and keep it typed by referencing a slice of the
shape in the docblock — the returned array is known to be the `context` object:

```php
/**
 * @return TraceJsonShape['context']
 */
public function getContext(): array
{
    return $this->attributes['context'];
}
```

At the call site the inner keys are still type-checked:

```php
$context = $shape->context;

$context['userId'];  // ok — string
$context['foobar'];  // PHPStan: offsetAccess.notFound
```

## Optional and nullable values

You're in full control of how missing data is handled. Return `null` for soft
access:

```php
public function tryException(): ?string
{
    return $this->attributes['exception'] ?? null;
}
```

…or require the key and fail loudly when it's absent:

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

The "try" pattern reads naturally at the call site:

```php
$exception = $shape->tryException();

if ($exception === null) {
    return null;
}

return [
    'exception'  => $exception,
    'stacktrace' => $shape->getStackTrace(),
];
```

::: tip
`JsonShapeException::missingKey()` captures the shape as context. See
[Common tools](/usage/common-tools#error-handling) for handling it.
:::

## Fluent access

Every shape exposes a Laravel
[`Fluent`](https://laravel.com/api/master/Illuminate/Support/Fluent.html)
instance via `$this->fluent`, handy for coercion and collections:

```php
public function getDuration(): int
{
    return $this->fluent->integer('duration');
}
```

```php
$duration      = $this->fluent->integer('duration');
$contextLength = $this->fluent->collect('context')->count();
$messageLength = $this->fluent->string('message')->length();
```

::: warning
Accessing values through `fluent` bypasses the static key-existence check that
typed `$this->attributes[...]` access gives you. Use it deliberately.
:::

## Array access

A shape implements `ArrayAccess`, so you can read it like the array it wraps:

```php
$shape['traceId'];        // get
isset($shape['traceId']); // exists
```

## Next

- [Setting properties](/usage/setting-properties)
- [Eloquent support](/usage/eloquent-support)
