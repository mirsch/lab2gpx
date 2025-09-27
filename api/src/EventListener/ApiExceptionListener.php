<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Kernel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use function json_encode;

readonly class ApiExceptionListener
{
    public function __construct(private Kernel $kernel)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = 500;
        $data = ['error' => 'Internal Server Error'];
        if ($this->kernel->getEnvironment() === 'dev') {
            $data['trace'] = json_encode($exception->getTrace());
            $data['message'] = $exception->getMessage();
        }

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $data['error'] = $exception->getStatusCode();
            $data['message'] = $exception->getMessage();
        }

        $response = new JsonResponse($data, $statusCode);
        $event->setResponse($response);
    }
}
