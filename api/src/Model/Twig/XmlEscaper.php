<?php

declare(strict_types=1);

namespace App\Model\Twig;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Runtime\EscaperRuntime;

use function array_keys;
use function htmlspecialchars;
use function in_array;
use function preg_replace;

use const ENT_XML1;

readonly class XmlEscaper implements EventSubscriberInterface
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
        if (in_array('xml', array_keys($escaper->getEscapers()))) {
            return;
        }

        $escaper->setEscaper('xml', static function (string $v) {
            if ($v === '') {
                return '';
            }

            // see https://github.com/mirsch/lab2gpx/issues/10, https://github.com/mirsch/lab2gpx/issues/100
            $v = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);

            return htmlspecialchars($v, ENT_XML1);
        });
    }
}
