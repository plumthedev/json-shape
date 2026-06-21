<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Fixtures;

use DateTime;
use DateTimeInterface;
use Plumthedev\JsonShape\Exceptions\JsonShapeException;
use Plumthedev\JsonShape\JsonShape;

/**
 * The running example shape used across the documentation.
 *
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

    /**
     * A writable hook exposes both get and set.
     *
     * @var array{userId: string, levelNumber: int}
     */
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
     * @param  TraceJsonShape['context']  $value
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
