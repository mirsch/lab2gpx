<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use function sprintf;

class RequestLogger
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (! $event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $this->logger->info(
            sprintf(
                '%s %s',
                $request->getMethod(),
                $request->getRequestUri(),
            ),
            ['content' => $request->getContent()],
        );
    }
}
