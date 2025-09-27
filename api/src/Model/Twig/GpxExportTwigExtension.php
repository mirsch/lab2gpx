<?php

declare(strict_types=1);

namespace App\Model\Twig;

use App\Model\DecimalMinutes;
use App\Model\Dto\Settings;
use App\Model\Util\Base31Util;
use Location\Coordinate;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function array_keys;
use function array_values;
use function assert;
use function count;
use function dechex;
use function in_array;
use function str_ireplace;
use function str_pad;
use function strtoupper;

use const STR_PAD_LEFT;

class GpxExportTwigExtension extends AbstractExtension
{
    /** @inheritDoc */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('shouldSkipStage', $this->shouldSkipStage(...)),
            new TwigFunction('formatStageForDisplay', $this->formatStageForDisplay(...)),
            new TwigFunction('getCode', $this->getCode(...)),
            new TwigFunction('decimalMinutes', $this->decimalMinutes(...)),
        ];
    }

    /** @param array<mixed> $geocacheSummary */
    public function shouldSkipStage(Settings $settings, array $geocacheSummary): bool
    {
        if (in_array($geocacheSummary['Title'], $settings->getNormalizedExcludeNames())) {
            return true;
        }

        if (in_array($geocacheSummary['Id'], $settings->getNormalizedExcludeUuids())) {
            return true;
        }

        return $geocacheSummary['IsComplete'] && ! in_array('0', $settings->completionStatuses);
    }

    /** @param array<mixed> $adventureLab */
    public function formatStageForDisplay(int $stage, array $adventureLab): string
    {
        return str_pad((string) $stage, count($adventureLab['GeocacheSummaries']) >= 10 ? 2 : 1, '0', STR_PAD_LEFT);
    }

    /** @param array<mixed> $adventureLab */
    private function getCustomCode(Settings $settings, array $adventureLab, int $stage): string
    {
        $marker = [
            '%CODE%' => $adventureLab['LAB2GPX_CODE'],
            '%STAGE_10%' => str_pad((string) $stage, 2, '0', STR_PAD_LEFT),
            '%STAGE_31%' => Base31Util::convertToBase31($stage),
            '%UUID%' => $adventureLab['Id'],
        ];
        assert($settings->customCodeTemplate !== null);

        return str_ireplace(array_keys($marker), array_values($marker), $settings->customCodeTemplate);
    }

    /** @param array<mixed> $adventureLab */
    public function getCode(
        Settings $settings,
        array $adventureLab,
        int $stage,
        bool $useStageAsPrefix = false,
    ): string {
        if ($settings->customCodeTemplate) {
            return $this->getCustomCode($settings, $adventureLab, $stage);
        }

        $stage = $stage ? str_pad((string) $stage, 2, '0', STR_PAD_LEFT) : 0;
        $prefix = $settings->prefix;
        if ($useStageAsPrefix) {
            $stage = strtoupper(dechex((int) $stage));
            $prefix = 'S' . $stage;
            if ($adventureLab['IsLinear']) {
                $prefix = 'L' . $stage;
            }

            $stage = 0;
        }

        $fixedPart = $adventureLab['LAB2GPX_CODE'];
        $sep = '-';
        if (! $settings->stageSeparator) {
            $sep = '';
        }

        $stage = Base31Util::convertToBase31((int) $stage);

        return strtoupper($prefix) . $fixedPart . ($stage ? $sep . $stage : '');
    }

    public function decimalMinutes(float $lat, float $lon): string
    {
        $coordinate = new Coordinate($lat, $lon);
        $formatter = new DecimalMinutes();

        return $coordinate->format($formatter);
    }
}
