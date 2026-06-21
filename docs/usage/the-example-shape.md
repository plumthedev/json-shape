# The example shape

Every page in the docs uses one shape: a `TraceShape` that models a structured
log trace stored in a JSON column. Define it once and the rest of the docs build
on it.

## The structure

The `@phpstan-type` definition is the single source of truth for the shape, and
`@extends JsonShape<…>` wires it into static analysis so every
`$this->attributes[...]` access inside the class is type-checked against it.

```php
<?php

namespace App\Shapes;

use DateTime;
use DateTimeInterface;
use Plumthedev\JsonShape\Exceptions\JsonShapeException;
use Plumthedev\JsonShape\JsonShape;

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
    // Read a value straight from the attributes.
    public string $traceId {
        get => $this->attributes['traceId'];
    }

    // Transform on the way out — cast to an enum.
    public LogLevelEnum $level {
        get => LogLevelEnum::from($this->attributes['level']);
    }

    // A writable hook exposes both get and set.
    public array $context {
        get => $this->attributes['context']; // @phpstan-ignore propertyGetHook.noRead
        set(array $value) => $this->attributes['context'] = $value;
    }

    // A plain getter works just as well.
    public function getMessage(): string
    {
        return $this->attributes['message'];
    }

    // Build an object inside the getter.
    public function getTimestamp(): DateTimeInterface
    {
        return new DateTime($this->attributes['timestamp']);
    }

    // Optional value: return null when absent.
    public function tryException(): ?string
    {
        return $this->attributes['exception'] ?? null;
    }

    // Or require the key and fail loudly.
    public function getStackTrace(): string
    {
        $trace = $this->attributes['stackTrace'] ?? null;

        if ($trace) {
            return $trace;
        }

        throw JsonShapeException::missingKey('stackTrace', $this);
    }

    // Coerce through Laravel's Fluent helper.
    public function getDuration(): int
    {
        return $this->fluent->integer('duration');
    }

    /**
     * Return a typed slice of the shape.
     *
     * @return TraceJsonShape['context']
     */
    public function getContext(): array
    {
        return $this->attributes['context'];
    }

    /**
     * Guarantee the structure being written.
     *
     * @param TraceJsonShape['context'] $value
     */
    public function setContext(array $value): self
    {
        return $this->tap(fn () => $this->attributes['context'] = $value);
    }

    // Set a simple value.
    public function setDuration(int $value): self
    {
        return $this->tap(fn () => $this->attributes['duration'] = $value);
    }
}
```

Each accessor here is explored in depth on the
[Reading](/usage/reading-properties) and [Setting](/usage/setting-properties)
properties pages.

The `level` field is backed by a plain PHP enum:

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

## How PHPStan reads the shape

Because the shape is typed, the array definition isn't just documentation — it
drives static analysis. A few examples of what that buys you:

| Declaration | What it means | What PHPStan enforces |
| --- | --- | --- |
| `traceId: string` | required string | key must exist; value is a `string` |
| `level: value-of<LogLevelEnum>` | one of the enum's backing values | only `'alert' \| 'info' \| 'debug'` |
| `exception?: string` | optional | may be absent; narrow before use |
| `stackTrace: string\|null` | nullable | present, but may be `null` |
| `context: array{...}` | nested object | inner keys are typed too |
| `duration?: int` | optional int | may be absent |

### A misspelled key is caught

Reading a key that isn't in the shape fails analysis before the code ever runs:

```php
public string $spanId {
    get => $this->attributes['spamId']; // PHPStan: offsetAccess.notFound
}
```

### Nested keys are typed too

The inner `context` object is just as strict as the top level:

```php
$context = $shape->context;

$context['userId'];  // ok — string
$context['foobar'];  // PHPStan: offsetAccess.notFound
```

### Subset types flow through your methods

You can reference a slice of the shape in a docblock so a method's input and
output stay tied to the source definition. Used on a getter, `@return` keeps the
returned array typed as the nested `context` object:

```php
/**
 * @return TraceJsonShape['context']
 */
public function getContext(): array
{
    return $this->attributes['context'];
}
```

…and on a setter, `@param` guarantees the structure passed in matches:

```php
/**
 * @param TraceJsonShape['context'] $value
 */
public function setContext(array $value): self
{
    return $this->tap(fn () => $this->attributes['context'] = $value);
}
```

This is the mechanism the rest of the docs lean on — once a shape is described,
its keys and value types are known everywhere it's read or written.

## Next

- [Reading properties](/usage/reading-properties)
- [Setting properties](/usage/setting-properties)
