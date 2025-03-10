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

namespace RoachPHP\Downloader\Middleware;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Psr\Log\LoggerInterface;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Support\Configurable;
use Spatie\Browsershot\Browsershot;

final class ExecuteJavascriptMiddleware implements RequestMiddlewareInterface
{
    use Configurable;

    /**
     * @var callable(string): Browsershot
     */
    private $getBrowsershot;

    /**
     * @param null|callable(string): Browsershot $getBrowsershot
     */
    public function __construct(
        private LoggerInterface $logger,
        ?callable $getBrowsershot = null,
    ) {
        /** @psalm-suppress MixedInferredReturnType, MixedReturnStatement */
        $this->getBrowsershot = $getBrowsershot ?? static fn (string $uri): Browsershot => Browsershot::url($uri)->waitUntilNetworkIdle();
    }

    public function handleRequest(Request $request): Request
    {
        $browsershot = $this->configureBrowsershot(
            $request->getUri(),
        );

        try {
            $body = $browsershot->bodyHtml();
        } catch (\Throwable $e) {
            $this->logger->info('[ExecuteJavascriptMiddleware] Error while executing javascript', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->getMeta('retry_count')) {
                throw $e;
            }

            return $request->drop('Error while executing javascript');
        }

        $psr7Response = new Psr7Response(200, [], $body);

        $response = new Response($psr7Response, $request);

        return $request->withResponse($response);
    }

    /**
     * @psalm-suppress MixedArgument, MixedAssignment
     */
    private function configureBrowsershot(string $uri): Browsershot
    {
        $browsershot = ($this->getBrowsershot)($uri);

        if (!empty($this->option('chromiumArguments'))) {
            $browsershot->addChromiumArguments($this->option('chromiumArguments'));
        }

        if (null !== ($chromePath = $this->option('chromePath'))) {
            $browsershot->setChromePath($chromePath);
        }

        if (null !== ($binPath = $this->option('binPath'))) {
            $browsershot->setBinPath($binPath);
        }

        if (null !== ($nodeModulePath = $this->option('nodeModulePath'))) {
            $browsershot->setNodeModulePath($nodeModulePath);
        }

        if (null !== ($includePath = $this->option('includePath'))) {
            $browsershot->setIncludePath($includePath);
        }

        if (null !== ($nodeBinary = $this->option('nodeBinary'))) {
            $browsershot->setNodeBinary($nodeBinary);
        }

        if (null !== ($npmBinary = $this->option('npmBinary'))) {
            $browsershot->setNpmBinary($npmBinary);
        }

        if (null !== ($userAgent = $this->option('userAgent'))) {
            $browsershot->userAgent($userAgent);
        }

        if (!empty($delay = $this->option('delay'))) {
            $browsershot->setDelay($delay);
        }

        return $browsershot;
    }

    private function defaultOptions(): array
    {
        return [
            'chromiumArguments' => [],
            'chromePath' => null,
            'binPath' => null,
            'nodeModulePath' => null,
            'includePath' => null,
            'nodeBinary' => null,
            'npmBinary' => null,
            'userAgent' => null,
            'delay' => 0,
        ];
    }
}
