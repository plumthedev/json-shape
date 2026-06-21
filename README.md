# JsonShape

> Give JSON a shape — typed objects for your database JSON columns in Laravel.

A JSON column comes back from Eloquent as a plain `array`: no autocomplete, no
static analysis, and a typo only blows up at runtime. **JsonShape** turns that
array into a typed object you define once, while staying a thin wrapper you can
still treat like an array.

```php
class Example extends Model
{
    public function casts(): array
    {
        return ['trace' => AsJsonShape::of(TraceShape::class)];
    }
}

$example->trace->traceId;       // string, with autocomplete
$example->trace->getDuration(); // int
$example->save();               // encoded back to JSON
```

## Installation

Requires PHP **8.4+** and **Laravel 13**.

```bash
composer require plumthedev/json-shape
```

## Documentation

Full guides and examples live at
**[plumthedev.github.io/json-shape](https://plumthedev.github.io/json-shape)**:

- [The example shape](https://plumthedev.github.io/json-shape/usage/the-example-shape)
- [Reading properties](https://plumthedev.github.io/json-shape/usage/reading-properties)
- [Setting properties](https://plumthedev.github.io/json-shape/usage/setting-properties)
- [Eloquent support](https://plumthedev.github.io/json-shape/usage/eloquent-support)
- [Common tools](https://plumthedev.github.io/json-shape/usage/common-tools)

## Development

This project is developed entirely inside Docker, so the only tools you need on
your machine are [Docker](https://docs.docker.com/get-docker/) (with the Compose
plugin) and [Make](https://www.gnu.org/software/make/).

```bash
make composer install      # install dependencies
make code-style-check      # Pint (code style)
make phpstan               # PHPStan (static analysis)
make phpunit               # PHPUnit (tests)
make docs-preview          # preview the docs site locally
```

The default PHP version is `8.4`; override it per command with `PHP_VERSION`
(`82`, `83`, `84`), e.g. `make phpunit PHP_VERSION=83`.

## License

Released under the [MIT License](LICENSE).
