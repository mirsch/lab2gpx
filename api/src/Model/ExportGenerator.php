<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Dto\Linear;
use App\Model\Dto\OutputFormat;
use App\Model\Dto\Settings;
use App\Model\Exception\BadGatewayHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Twig\Environment;

use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function in_array;
use function json_decode;
use function json_encode;
use function preg_match_all;
use function preg_replace_callback;
use function set_time_limit;
use function strtoupper;
use function time;

use const JSON_PRETTY_PRINT;

class ExportGenerator
{
    private Settings $settings;

    public function __construct(
        private readonly AdventureLabApiClient $adventureLabApiClient,
        private readonly Environment $twig,
        private readonly LocaleAwareInterface $localeProvider,
        private readonly string $cacheDir,
        private readonly int $cacheTtl,
        private readonly AdventureLabDatabase $labCode,
    ) {
    }

    public function generateFile(Settings $settings): string
    {
        $this->settings = $settings;
        $this->localeProvider->setLocale($settings->locale->value);
        $labs = [];
        $this->fetchLabs($labs);

        switch ($settings->outputFormat) {
            case OutputFormat::GPX:
            case OutputFormat::ZIPPED_GPX:
                $template = 'gpx.xml.twig';
                break;
            case OutputFormat::GPX_WPT:
            case OutputFormat::ZIPPED_GPX_WPT:
                $template = 'gpxwaypoints.xml.twig';
                break;
            case OutputFormat::CACHETURDOTNO:
                $template = 'cacheturdotno.csv.twig';
                break;
        }

        $file = $this->twig->render($template, [
            'settings' => $settings,
            'adventureLabs' => $labs,
        ]);

        if ($this->settings->quirksBomForCsv && $this->settings->outputFormat === OutputFormat::CACHETURDOTNO) {
            $file = "\xEF\xBB\xBF" . $file;
        }

        return $file;
    }

    /** @return array<mixed>|null */
    private function getCachedAdventureLab(string $id): array|null
    {
        $file = $this->cacheDir . '/' . $id . '.json';
        if (file_exists($file) && filemtime($file) > time() - $this->cacheTtl) {
            $json = @file_get_contents($file);
            if (! $json) {
                return null;
            }

            $result = json_decode($json, true);
            if (! $result) {
                return null;
            }

            return $result;
        }

        return null;
    }

    /** @param array<mixed> $adventureLab */
    private function saveCachedAdventureLab(array $adventureLab): void
    {
        $file = $this->cacheDir . '/' . $adventureLab['Id'] . '.json';
        file_put_contents($file, json_encode($adventureLab, JSON_PRETTY_PRINT));
    }

    /** @param array<mixed> $adventureLabSearchItem */
    private function shouldSkipAdventureLabSearchItem(array $adventureLabSearchItem): bool
    {
        if (in_array($adventureLabSearchItem['Title'], $this->settings->getNormalizedExcludeNames())) {
            return true;
        }

        return in_array($adventureLabSearchItem['Id'], $this->settings->getNormalizedExcludeUuids());
    }

    /**
     * this information is not in the search result, so we have to filter after fetching the details
     *
     * @param array<mixed> $adventureLab
     */
    private function shouldSkipAdventureLab(array $adventureLab): bool
    {
        if ($adventureLab['IsLinear'] && $this->settings->linear === Linear::IGNORE) {
            return true;
        }

        return in_array($adventureLab['OwnerUsername'], $this->settings->getNormalizedExcludeOwners());
    }

    /** @param string[] $codes */
    private function findAndLinkGcCodes(string $text, array &$codes): string
    {
        $pattern = '/(?<![A-Za-z0-9])(GC[0-9A-Z]{3,8})(?![A-Za-z0-9])/i';
        preg_match_all($pattern, $text, $matches);
        $codes = array_merge($codes, array_map('strtoupper', $matches[1]));
        $codes = array_values(array_unique($codes));

        return (string) preg_replace_callback(
            $pattern,
            static function ($match) {
                $code = strtoupper($match[1]);

                return '<a href="https://coord.info/' . $code . '" target="_blank">' . $code . '</a>';
            },
            $text,
        );
    }

    /**
     * @param array<mixed> $adventureLab
     *
     * @return array<mixed>
     */
    private function enrichAdventureLab(array $adventureLab): array
    {
        $adventureLab = $this->labCode->updateAdventureLabData($adventureLab);

        $gccodes = [];
        $adventureLab['LAB2GPX_Description'] = $this->findAndLinkGcCodes($adventureLab['Description'], $gccodes);
        foreach ($adventureLab['GeocacheSummaries'] as $k => $geocacheSummary) {
            $adventureLab['GeocacheSummaries'][$k]['LAB2GPX_Description'] = $this->findAndLinkGcCodes($geocacheSummary['Description'], $gccodes);
        }

        $adventureLab['LAB2GPX_GCCodes'] = $gccodes;

        return $adventureLab;
    }

    /** @param array<mixed> $labs */
    private function fetchLabs(array &$labs, int $skip = 0): void
    {
        $take = 300;
        $response = $this->adventureLabApiClient->searchV4($this->settings->coordinates, $this->settings->radius, $this->settings->completionStatuses, $this->settings->userGuid, $skip, $take);
        if (! isset($response['Items']) || ! count($response['Items']) > 0) {
            throw new NotFoundHttpException('No Adventure Labs in Search Result');
        }

        foreach ($response['Items'] as $adventureLabSearchItem) {
            if ($this->settings->limit > 0 && count($labs) >= $this->settings->limit) {
                return;
            }

            if ($this->shouldSkipAdventureLabSearchItem($adventureLabSearchItem)) {
                continue;
            }

            $fileCacheEnabled = true;
            // partially complete, we can only get this if you call this using a user guid, so do not cache this Adventure Lab result
            if ($adventureLabSearchItem['CompletionStatus'] === 1) {
                $fileCacheEnabled = false;
            }

            if ($fileCacheEnabled) {
                $cachedLab = $this->getCachedAdventureLab($adventureLabSearchItem['Id']);
                if ($cachedLab) {
                    $labs[] = $cachedLab;
                    continue;
                }
            }

            @set_time_limit(10);

            try {
                $adventureLab = $this->adventureLabApiClient->getAdventureById($adventureLabSearchItem['Id'], $this->settings->userGuid);
            } catch (BadGatewayHttpException $exception) {
                if ($exception->getCode() === BadGatewayHttpException::POSSIBLE_ARCHIVED) {
                    continue;
                }

                throw $exception;
            }

            // just a very basic test, we have at least an Id in the array and not an error message in status code 200
            if (! isset($adventureLab['Id'])) {
                continue;
            }

            $adventureLab = $this->enrichAdventureLab($adventureLab);

            if ($fileCacheEnabled) {
                $this->saveCachedAdventureLab($adventureLab);
            }

            if ($this->shouldSkipAdventureLab($adventureLab)) {
                continue;
            }

            $labs[] = $adventureLab;
        }

        $total = (int) $response['TotalCount'];
        if (count($labs) >= $total || $skip + $take >= $total) {
            return;
        }

        $this->fetchLabs($labs, $skip + $take);
    }
}
