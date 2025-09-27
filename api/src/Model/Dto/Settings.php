<?php

declare(strict_types=1);

namespace App\Model\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

use function array_filter;
use function array_map;
use function array_unique;
use function count;
use function explode;
use function str_replace;

#[Assert\GroupSequenceProvider]
class Settings implements GroupSequenceProviderInterface
{
    public Locale $locale = Locale::EN;

    #[Assert\NotBlank]
    public Coordinates $coordinates;

    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    public float $radius = 15;

    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    public int $limit = 300;
    public CacheType $cacheType = CacheType::LAB_CACHE;
    public Linear $linear = Linear::DEFAULT;

    // code generation
    #[Assert\NotBlank(groups: ['WithoutCustomCodeTemplate'])]
    #[Assert\Length(min: 1, max: 3, groups: ['WithoutCustomCodeTemplate'])]
    public string $prefix = 'LC';
    public bool $stageSeparator = true;
    public string|null $customCodeTemplate = null;

    // filters/personalized search
    #[Assert\Uuid]
    public string|null $userGuid = null;

    /** @var string[] */
    #[Assert\Count(max: 3)]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Choice(choices: ['0', '1', '2']),
    ])]
    public array $completionStatuses = ['0', '1', '2'];

    // description generation
    public bool $includeQuestion = true;
    public bool $includeWaypointDescription = true;
    public bool $includeCacheDescription = true;

    // exlusions
    private string|null $excludeOwner = null;
    private string|null $excludeNames = null;
    private string|null $excludeUuids = null;

    public function getExcludeOwner(): string|null
    {
        return $this->excludeOwner;
    }

    public function setExcludeOwner(string|null $excludeOwner): Settings
    {
        $this->excludeOwner = $excludeOwner;
        $this->normalizedExcludeOwners = null;

        return $this;
    }

    public function getExcludeNames(): string|null
    {
        return $this->excludeNames;
    }

    public function setExcludeNames(string|null $excludeNames): Settings
    {
        $this->excludeNames = $excludeNames;
        $this->normalizedExcludeNames = null;

        return $this;
    }

    public function getExcludeUuids(): string|null
    {
        return $this->excludeUuids;
    }

    public function setExcludeUuids(string|null $excludeUuids): Settings
    {
        $this->excludeUuids = $excludeUuids;
        $this->normalizedExcludeUuids = null;

        return $this;
    }

    public bool $quirksL4Ctype = false;
    public bool $quirksBomForCsv = false;
    public OutputFormat $outputFormat = OutputFormat::ZIPPED_GPX;

    public function getGroupSequence(): array|GroupSequence
    {
        $groups = ['Settings'];

        if ($this->customCodeTemplate === null) {
            $groups[] = 'WithoutCustomCodeTemplate';
        }

        return $groups;
    }

    #[Assert\Callback]
    public function validateCompletionStatusesUnique(ExecutionContextInterface $context): void
    {
        $values = $this->completionStatuses;
        if (count($values) === count(array_unique($values))) {
            return;
        }

        $context->buildViolation('Jeder Status darf nur einmal vorkommen.')
            ->atPath('completionStatuses')
            ->addViolation();
    }

    /** @var string[]|null */
    private array|null $normalizedExcludeOwners = null;

    /** @var string[]|null */
    private array|null $normalizedExcludeNames = null;

    /** @var string[]|null */
    private array|null $normalizedExcludeUuids = null;

    /** @return string[] */
    private function splitExludeField(string $string): array
    {
        $string = str_replace("\r\n", "\n", $string);
        $string = str_replace("\r", "\n", $string);
        $string = str_replace(',', "\n", $string);
        $string = str_replace(';', "\n", $string);

        return array_unique(array_map('trim', array_filter(explode("\n", $string))));
    }

    /** @return string[] */
    public function getNormalizedExcludeOwners(): array
    {
        if ($this->normalizedExcludeOwners !== null) {
            return $this->normalizedExcludeOwners;
        }

        $this->normalizedExcludeOwners = $this->excludeOwner ? $this->splitExludeField($this->excludeOwner) : [];

        return $this->normalizedExcludeOwners;
    }

    /** @return string[] */
    public function getNormalizedExcludeNames(): array
    {
        if ($this->normalizedExcludeNames !== null) {
            return $this->normalizedExcludeNames;
        }

        $this->normalizedExcludeNames = $this->excludeNames ? $this->splitExludeField($this->excludeNames) : [];

        return $this->normalizedExcludeNames;
    }

    /** @return string[] */
    public function getNormalizedExcludeUuids(): array
    {
        if ($this->normalizedExcludeUuids !== null) {
            return $this->normalizedExcludeUuids;
        }

        $this->normalizedExcludeUuids = $this->excludeUuids ? $this->splitExludeField($this->excludeUuids) : [];

        return $this->normalizedExcludeUuids;
    }
}
