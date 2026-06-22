# Chapter 5 — Create & combine shapes

> **The question:** how do I build shapes by hand — in tests, jobs, and
> transformers — away from Eloquent?

[Chapter 4](/usage/eloquent-casting) covered the common path: a model loads and
the cast hands you a shape. But shapes are useful on their own too. This chapter
covers creating them from scratch, combining them, copying them, and turning them
back into arrays or JSON.

Everything builds on the [`TraceShape`](/usage/defining-a-shape) from Chapter 1.

## Creating shapes

Three factory methods cover every starting point:

```php
// from an array — the everyday case
$trace = TraceShape::make([
    'traceId' => 'abc-123',
    'message' => 'Request handled',
    'context' => ['userId' => 'u-1', 'levelNumber' => 1],
]);

// from a raw JSON string — e.g. a webhook body or a cached payload
$trace = TraceShape::fromJson($json);

// an empty shape to fill in later
$trace = TraceShape::empty();
```

These are the only way to construct a shape directly — recall from
[Chapter 1](/usage/defining-a-shape#you-dont-new-a-shape) that the constructor is
`protected`. `make()` is what an Eloquent cast calls under the hood, so a
hand-built shape behaves identically to a loaded one.

## Merging

`merge()` combines data using **dot notation** and accepts either an array or
another shape. It returns the shape, so calls chain:

```php
$trace = TraceShape::empty()
    ->merge(['context.userId' => 'superSimple'])
    ->merge($shapeOne)
    ->merge($shapeTwo);
```

::: warning Pitfall
`merge()` mutates the shape **in place** and returns the same instance — it does
not produce a new shape. If you need to keep the original intact, `clone()` it
first (next section).
:::

## Cloning

`clone()` returns an independent copy. Mutating the copy never touches the
original — the fix for the pitfall above:

```php
$copy = $trace->clone();

$copy->setDuration(777); // $trace is untouched
```

A typical pattern is *clone, then merge* to derive a variant safely:

```php
$slow = $trace->clone()->merge(['duration' => 5000]);
```

## Serializing

A shape implements `Arrayable`, `Jsonable`, and `JsonSerializable`, so it
converts cleanly back to an array or JSON wherever Laravel expects one — API
resources, queued job payloads, log context:

```php
$shape->toArray();   // array
$shape->toJson();    // JSON string
json_encode($shape); // JSON string — JsonSerializable kicks in
```

## Putting it together

A realistic example — build a trace inside a job and ship it as JSON:

```php
$trace = TraceShape::make([
    'traceId' => (string) Str::uuid(),
    'message' => 'Order processed',
    'context' => ['userId' => $order->user_id, 'levelNumber' => 6],
])->merge(['duration' => $stopwatch->elapsed()]);

Log::info('trace', $trace->toArray());
```

## Best practices

- **Use `make()` in tests** to get a typed shape without touching the database.
- **Clone before merging** whenever the original must survive.
- **Lean on `toArray()` / `toJson()`** at your app's boundaries instead of
  reaching into `$attributes`.

---

**Previous:** [Chapter 4 — Cast it on a model](/usage/eloquent-casting) ·
**Next:** [Chapter 6 — Type safety in depth →](/usage/type-safety)
