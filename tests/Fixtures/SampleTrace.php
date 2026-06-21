<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Fixtures;

/**
 * Canonical trace payload shared across the test suite.
 *
 * @phpstan-import-type TraceJsonShape from TraceShape
 */
final class SampleTrace
{
    /**
     * A fully populated, valid trace payload.
     *
     * @return TraceJsonShape
     */
    public static function payload(): array
    {
        return [
            'traceId' => 'abc-123',
            'spanId' => 'span-1',
            'level' => 'debug',
            'message' => 'Request handled',
            'timestamp' => '2026-06-21 12:00:00',
            'stackTrace' => null,
            'context' => [
                'userId' => 'u-1',
                'levelNumber' => 1,
            ],
        ];
    }
}
