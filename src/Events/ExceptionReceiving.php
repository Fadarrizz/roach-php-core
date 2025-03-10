<?php

declare(strict_types=1);

/**
 * Copyright (c) 2025 Auke Geerts
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/roach-php/roach
 */

namespace RoachPHP\Events;

use Exception;
use Symfony\Contracts\EventDispatcher\Event;

final class ExceptionReceiving extends Event
{
    public const NAME = 'exception.receiving';

    public function __construct(public Exception $exception)
    {
    }
}
