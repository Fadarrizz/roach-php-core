<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/roach-php/roach
 */

namespace RoachPHP\Http;

use GuzzleHttp\Exception\GuzzleException;

final class RequestException extends \Exception implements RequestExceptionInterface
{
    public function __construct(
        private Request $request,
        private GuzzleException $reason,
    ) {
        parent::__construct('An exception occurred while sending a request', previous: $reason);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getReason(): GuzzleException
    {
        return $this->reason;
    }
}
