# Common tools

Beyond reading and writing, a shape comes with helpers for creating, combining,
serializing, and extending it. This page collects them.

All examples build on the [`TraceShape`](/usage/the-example-shape) defined
earlier.

## Creating shapes

Outside of Eloquent, build shapes directly with the factory methods:

```php
// from an array
$trace = TraceShape::make([
    'traceId' => 'abc-123',
    'message' => 'Request handled',
    'context' => ['userId' => 'u-1', 'levelNumber' => 1],
]);

// from a raw JSON string
$trace = TraceShape::fromJson($json);

// an empty shape to fill in later
$trace = TraceShape::empty();
```

## Merging

`merge()` combines data using dot notation and accepts either an array or
another shape. It returns the shape, so calls chain:

```php
$trace = TraceShape::empty()
    ->merge(['context.userId' => 'superSimple'])
    ->merge($shapeOne)
    ->merge($shapeTwo);
```

## Cloning

`clone()` returns an independent deep copy, so mutating one shape never affects
the other:

```php
$copy = $trace->clone();

$copy->setDuration(777); // $trace is untouched
```

## Serializing

A shape implements `Arrayable`, `Jsonable` and `JsonSerializable`, so it
converts cleanly to an array or JSON:

```php
$shape->toArray();   // array
$shape->toJson();    // JSON string
json_encode($shape); // JSON string
```

## Laravel helpers

`JsonShape` pulls in Laravel's `Conditionable`, `Tappable`, `Macroable` and
`Dumpable` traits, so the helpers you already use are available out of the box.

```php
// Conditionable — when() / unless()
$shape->when($condition, fn (TraceShape $shape) => $shape->merge(['examples.whenPassed' => true]));
$shape->unless($condition, fn (TraceShape $shape) => $shape->merge(['examples.unlessPassed' => true]));

// Tappable — run a side effect, keep the shape
$shape->tap(fn (TraceShape $shape) => $shape->merge(['examples.tapped' => true]));

// Macroable — extend a shape with your own methods
TraceShape::macro('isDebug', fn () => $this->level === LogLevelEnum::DEBUG);
TraceShape::macro('isSlowRequest', fn (int $thresholdMs = 1000) => $this->getDuration() > $thresholdMs);

$shape->isDebug();
$shape->isSlowRequest(2000);

// Dumpable — debug inline
$shape->dump();
$shape->dd();
```

## Error handling

When a required key is missing, throw a `JsonShapeException` with
`missingKey()` — it captures the shape as context so failures are easy to trace:

```php
use Plumthedev\JsonShape\Exceptions\JsonShapeException;

throw JsonShapeException::missingKey('stackTrace', $this);
```

The attached context is available on the caught exception:

```php
try {
    $shape->getStackTrace();
} catch (JsonShapeException $e) {
    report($e->getContext());
}
```
