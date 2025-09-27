<?php

declare(strict_types=1);

namespace App\Model\Twig;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Runtime\EscaperRuntime;

use function array_keys;
use function in_array;
use function str_replace;
use function strpbrk;

readonly class CsvEscaper implements EventSubscriberInterface
{
    public function __construct(private Environment $twig)
    {
    }

    /** @inheritDoc */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['register', 2048]],
            ConsoleEvents::COMMAND => 'register',
        ];
    }

    public function register(): void
    {
        $escaper = $this->twig->getRuntime(EscaperRuntime::class);
        if (in_array('csv', array_keys($escaper->getEscapers()))) {
            return;
        }

        $escaper->setEscaper('csv', static function (string $v) {
            if ($v === '') {
                return '';
            }

            // RFC-4180
            // - double quoates
            // - Comma, CR, LF oor Quotes -> wrap in "
            $needsQuotes = strpbrk($v, ",\r\n\"") !== false;

            $v = str_replace('"', '""', $v);

            return $needsQuotes ? '"' . $v . '"' : $v;
        });
    }
}
