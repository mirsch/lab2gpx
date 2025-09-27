<?php

declare(strict_types=1);

namespace App\Model\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BadGatewayHttpException extends HttpException
{
    public const int POSSIBLE_ARCHIVED = 99;

    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct(502, $message, code: $code);
    }
}
