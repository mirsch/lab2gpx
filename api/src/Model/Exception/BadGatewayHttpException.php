<?php

declare(strict_types=1);

namespace App\Model\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BadGatewayHttpException extends HttpException
{
    public const int POSSIBLE_ARCHIVED = 99;
    public const int LODESTONE_ERROR = 98;

    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct(502, $message, code: $code);
    }

    /**
     * errors affecting only a single adventure lab, which can be skipped without aborting the whole export
     */
    public function isSkippableAdventureError(): bool
    {
        return $this->getCode() === self::POSSIBLE_ARCHIVED || $this->getCode() === self::LODESTONE_ERROR;
    }
}
