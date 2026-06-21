<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests\Fixtures;

enum LogLevelEnum: string
{
    case ALERT = 'alert';
    case INFO = 'info';
    case DEBUG = 'debug';
}
