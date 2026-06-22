# JsonShape

> **Your Laravel JSON columns deserve types.** JsonShape turns an untyped JSON
> column into a real, typed object — with autocomplete, static analysis, and
> methods — without giving up the array you already know.

[![Latest Version](https://img.shields.io/packagist/v/plumthedev/json-shape.svg?style=flat-square)](https://packagist.org/packages/plumthedev/json-shape)
[![Total Downloads](https://img.shields.io/packagist/dt/plumthedev/json-shape.svg?style=flat-square)](https://packagist.org/packages/plumthedev/json-shape)
[![PHP Version](https://img.shields.io/packagist/php-v/plumthedev/json-shape.svg?style=flat-square)](https://packagist.org/packages/plumthedev/json-shape)
[![License](https://img.shields.io/packagist/l/plumthedev/json-shape.svg?style=flat-square)](LICENSE)

## The 2am problem

You reach for a JSON column because the data is structured but doesn't deserve
its own table — user preferences, an API response you cached, audit context, a
feature-flag payload. It works. Then six months later:

```php
$user->preferences['notifcations']['email'];  // typo. ships green. pages you at 2am.
```

Eloquent hands JSON columns back as a plain `array`. That means **no
autocomplete, no static analysis, and no guarantees**. The structure lives only
in your head and in a migration comment nobody reads. Every access is a leap of
faith — was it `email` or `emails`? is `theme` always set? did that last
deployment change the shape? — and the answer arrives in production, as an
`undefined array key` deep inside a controller.

You've been compensating with accessors, `data_get()` calls, defensive `??`
chains, and `json_decode` sprinkled across your models. It's noise, and it still
doesn't make the data *typed*.

## Meet JsonShape

Describe the structure **once**, as a class. From then on, Eloquent hands you a
typed object instead of a bare array — and your IDE and PHPStan finally
understand what's inside your JSON.

```php
$user->preferences->theme;          // string — autocompleted, type-checked
$user->preferences->emailEnabled(); // bool — and that 2am typo? now a PHPStan error
$user->save();                      // encoded back to JSON automatically
```

Same column. Same database. The only thing that changed: the mistakes you used
to ship now fail in your editor and your CI, before a user ever hits them.

## Before and after

| | Plain `array` cast | With JsonShape |
| --- | --- | --- |
| Misspelled key | breaks at runtime | **caught by PHPStan** |
| Autocomplete | none | **every field** |
| Missing/optional fields | silent `null` or fatal | **typed, explicit** |
| Reading the shape | scroll to the migration | **read the class** |
| Behaves like an array | yes | **still yes** |

```php
// Before — flying blind.
$prefs = $user->preferences;            // array
$prefs['theme'] ?? 'light';             // is "theme" a real key? you guess.
$prefs['notifcations']['email'];        // typo compiles fine. fails for a user.

// After — typed, the same data.
$user->preferences->theme;              // string
$user->preferences->emailEnabled();     // bool
$user->preferences['theme'];            // array access still works when you want it
```

## "Why not just use an array?"

The honest answer to the most common objection:

- **"It's just an array cast, why add a class?"** An array cast gives you data
  with zero guarantees. The moment that JSON is read in more than one place, the
  structure becomes tribal knowledge. A shape makes it a single, type-checked
  source of truth — and PHPStan enforces it for free.
- **"Can't I write accessors for this?"** You can, and you end up with mutators,
  `json_decode`, and `data_get()` scattered across the model. JsonShape is one
  cast and one class; reads and writes flow through it automatically.
- **"Isn't a full DTO/validation library better?"** Sometimes — if you need
  request validation, transformers, and mapping, reach for one. JsonShape is
  deliberately smaller: a **thin, ~230-line wrapper** that *stays an array* while
  adding types. No mapping layer, no hidden state, nothing to fight when you
  debug. It does one thing.
- **"Will it slow my model down?"** It's a value object over the same decoded
  array Eloquent already produces. No reflection, no magic.

## Where teams use it

JsonShape pays off anywhere a JSON column has a knowable shape that's read in
more than one place:

- **User / tenant settings** — `preferences`, `notification_settings`, theme and locale.
- **Cached or denormalized API payloads** — a typed view over a third-party response.
- **Audit & activity context** — structured metadata attached to log or event rows.
- **Feature-flag / experiment payloads** — typed access to rollout configuration.
- **Structured domain blobs** — order snapshots, pricing breakdowns, traces.

## Quick start

Requires PHP **8.4+** and **Laravel 13**.

```bash
composer require plumthedev/json-shape
```

**1. Describe the shape.** The `@phpstan-type` block is the single source of
truth; expose values as typed property hooks or getters.

```php
namespace App\Shapes;

use Plumthedev\JsonShape\JsonShape;

/**
 * @phpstan-type PreferencesData array{
 *     theme: string,
 *     emailEnabled: bool,
 *     locale?: string,
 * }
 *
 * @extends JsonShape<PreferencesData>
 */
class PreferencesShape extends JsonShape
{
    public string $theme {
        get => $this->attributes['theme'];
    }

    public function emailEnabled(): bool
    {
        return $this->fluent->boolean('emailEnabled');
    }

    public function setTheme(string $value): self
    {
        return $this->tap(fn () => $this->attributes['theme'] = $value);
    }
}
```

**2. Cast it on the model.**

```php
use App\Shapes\PreferencesShape;
use Plumthedev\JsonShape\Casts\AsJsonShape;

class User extends Model
{
    public function casts(): array
    {
        return ['preferences' => AsJsonShape::of(PreferencesShape::class)];
    }
}
```

**3. Use it like an object.**

```php
$user = User::find(1);

$user->preferences->theme;          // string, typed
$user->preferences->emailEnabled(); // bool

$user->preferences->setTheme('dark');
$user->save();
```

> Need a generic, untyped shape? Cast with `AsJsonShape::class` and you'll get a
> plain `JsonShape` back — still array-accessible, just without a dedicated class.

## When to use it — and when not to

**Reach for JsonShape when:**

- A JSON column has a known, fairly stable structure you read in more than one place.
- You run PHPStan/Larastan and want your JSON access verified like the rest of your code.
- You'd otherwise be writing accessors or scattering `json_decode` across models.

**Skip it when:**

- The data is genuinely schemaless or wildly varied — a plain `array`/`collection` cast is simpler.
- You need a relational schema with constraints and indexes — that's a table, not a JSON column.
- You only ever touch one field once — the typing overhead isn't worth it.

We'd rather you not install it than fight it. If your case is in the "skip"
column, the docs say so plainly.

## Documentation

Full guides and runnable examples live at
**[plumthedev.github.io/json-shape](https://plumthedev.github.io/json-shape)** —
a set of short, progressive chapters:

1. [Define a shape](https://plumthedev.github.io/json-shape/usage/defining-a-shape)
2. [Read values](https://plumthedev.github.io/json-shape/usage/reading-values)
3. [Write values](https://plumthedev.github.io/json-shape/usage/writing-values)
4. [Cast it on a model](https://plumthedev.github.io/json-shape/usage/eloquent-casting)
5. [Create & combine shapes](https://plumthedev.github.io/json-shape/usage/creating-shapes)
6. [Type safety in depth](https://plumthedev.github.io/json-shape/usage/type-safety)
7. [Helpers, macros & errors](https://plumthedev.github.io/json-shape/usage/helpers)

## The bigger idea

Laravel gave us first-class casts, property hooks, and a strong static-analysis
story with Larastan. Typed JSON columns are the obvious next step — and there's
no agreed-on, ergonomic way to do it yet. JsonShape is an **opinionated attempt
to define that pattern**: types that PHPStan enforces, an object that still feels
like the array underneath, and as little machinery as possible in between.

It's early, and the conventions are still being shaped — which is exactly why
your input matters. If you have a strong opinion about how typed JSON in Laravel
*should* feel, this is a great time to help set the direction.

## Contributing

Contributions, issues, and ideas are all welcome. Open an
[issue](https://github.com/plumthedev/json-shape/issues) to discuss a change or
challenge a design decision, or send a pull request.

The whole toolchain runs in Docker, so all you need locally is
[Docker](https://docs.docker.com/get-docker/) (with the Compose plugin) and
[Make](https://www.gnu.org/software/make/):

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
