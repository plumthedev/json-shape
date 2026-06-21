# Setting properties

Writing values mirrors reading them: you expose setters that mutate the
underlying attributes, and you choose how strict they are. This page covers
standard setters, writable property hooks, and array access.

All examples build on the [`TraceShape`](/usage/the-example-shape) defined
earlier.

## Standard setters

A setter mutates `$this->attributes`. Returning `$this->tap(...)` keeps it
chainable while making the mutation explicit. Reference a slice of the shape
with `@param` so the structure passed in is checked against the source
definition:

```php
public function setDuration(int $value): self
{
    return $this->tap(fn () => $this->attributes['duration'] = $value);
}

/**
 * @param TraceJsonShape['context'] $value
 */
public function setContext(array $value): self
{
    return $this->tap(fn () => $this->attributes['context'] = $value);
}
```

Call them individually or chain them:

```php
$shape
    ->setDuration(504)
    ->setContext([
        'userId'      => 'e92fd2c9',
        'levelNumber' => 16,
    ]);
```

Because `setContext()` is typed with `TraceJsonShape['context']`, passing the
wrong value type is caught by analysis:

```php
$shape->context = [
    'userId'      => null, // PHPStan: offset 'userId' (string) does not accept null
    'levelNumber' => 16,
];
```

::: warning
Unexpected *extra* keys passed to a setter aren't rejected by PHPStan yet —
always set exactly the keys your shape declares.
:::

## Writable property hooks

A property hook can also expose a setter. This works today, though a typed
writable hook over an offset currently needs a PHPStan ignore on the `get`
side:

```php
public array $context {
    get => $this->attributes['context']; // @phpstan-ignore propertyGetHook.noRead
    set(array $value) => $this->attributes['context'] = $value;
}
```

```php
$shape->context = [
    'userId'      => 'user-id',
    'levelNumber' => 16,
];
```

## Array access

A shape implements `ArrayAccess`, so you can also write to it like an array:

```php
$shape['traceId'] = 'abc'; // set
unset($shape['traceId']);  // remove
```

## Next

- [Eloquent support](/usage/eloquent-support)
- [Common tools](/usage/common-tools)
