# Chapter 3 — Write values

> **The question:** how do I change a value on a shape and keep it type-checked?

Writing mirrors [reading](/usage/reading-values): you expose setters that mutate
the underlying attributes, and you decide how strict they are. This chapter
covers the three ways to write, and how mutation interacts with the rest of the
package.

Everything builds on the [`TraceShape`](/usage/defining-a-shape) from Chapter 1.

## 1. Setters — the default

A setter mutates `$this->attributes`. Returning `$this->tap(...)` makes the
mutation explicit while keeping the call chainable:

```php
public function setDuration(int $value): self
{
    return $this->tap(fn () => $this->attributes['duration'] = $value);
}
```

For nested data, reference a slice of the shape with `@param` so the structure
you pass in is checked against the source definition:

```php
/**
 * @param TraceJsonShape['context'] $value
 */
public function setContext(array $value): self
{
    return $this->tap(fn () => $this->attributes['context'] = $value);
}
```

Because the setters return `$this`, they chain naturally:

```php
$shape
    ->setDuration(504)
    ->setContext([
        'userId'      => 'e92fd2c9',
        'levelNumber' => 16,
    ]);
```

And because `setContext()` is typed with `TraceJsonShape['context']`, passing the
wrong value type fails static analysis before it ever runs:

```php
$shape->setContext([
    'userId'      => null, // PHPStan: offset 'userId' (string) does not accept null
    'levelNumber' => 16,
]);
```

::: warning Pitfall
PHPStan does **not** reject unexpected *extra* keys passed to a setter yet — only
wrong types on declared keys. Always set exactly the keys your shape declares.
:::

## 2. Writable property hooks

A property hook can expose a `set` alongside its `get`. This works today, with one
caveat: a typed writable hook over an array offset currently needs a PHPStan
ignore on the `get` side.

```php
public array $context {
    get => $this->attributes['context']; // @phpstan-ignore propertyGetHook.noRead
    set(array $value) => $this->attributes['context'] = $value;
}
```

```php
$shape->context = ['userId' => 'user-id', 'levelNumber' => 16];
```

Setters are the more common choice; reach for a writable hook when you want
field-like assignment syntax.

## 3. Array access

A shape implements `ArrayAccess`, so you can write to it like an array — handy in
tests and quick scripts:

```php
$shape['traceId'] = 'abc'; // set
unset($shape['traceId']);  // remove
```

## How mutation fits the bigger picture

A shape is **mutable**: setters change the object in place. Two consequences worth
internalising now:

- **On an Eloquent model, a mutation isn't saved until you call `$model->save()`.**
  Changing the shape changes the in-memory object; persistence is a separate step.
  [Chapter 4](/usage/eloquent-casting) shows the full cycle.
- **`merge()` also mutates in place**, while **`clone()`** gives you an
  independent copy. When you need to change a shape without touching the original,
  clone first. [Chapter 5](/usage/creating-shapes) covers both.

## Best practices

- **Prefer setters returning `$this->tap(...)`** — explicit mutation, free
  chaining.
- **Type nested setters with `TraceJsonShape['…']`** so analysis guards the
  structure you write.
- **Set exactly the declared keys** — extra keys slip past the checker for now.

---

**Previous:** [Chapter 2 — Read values](/usage/reading-values) ·
**Next:** [Chapter 4 — Cast it on a model →](/usage/eloquent-casting)
