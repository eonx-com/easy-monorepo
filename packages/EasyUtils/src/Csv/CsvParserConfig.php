<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

final class CsvParserConfig implements CsvParserConfigInterface
{
    /**
     * @var null|string[]
     */
    private ?array $groupPrefixes;

    /**
     * @var null|string[]
     */
    private ?array $requiredHeaders;

    /**
     * @param null|string[] $requiredHeaders
     * @param null|string[] $groupPrefixes
     */
    public function __construct(?array $requiredHeaders = null, ?array $groupPrefixes = null)
    {
        $this->requiredHeaders = $requiredHeaders;
        $this->groupPrefixes = $groupPrefixes;
    }

    /**
     * @param null|string[] $requiredHeaders
     * @param null|string[] $groupPrefixes
     */
    public static function create(?array $requiredHeaders = null, ?array $groupPrefixes = null): self
    {
        return new self($requiredHeaders, $groupPrefixes);
    }

    /**
     * @return null|string[]
     */
    public function getGroupPrefixes(): ?array
    {
        return $this->groupPrefixes;
    }

    /**
     * @return null|string[]
     */
    public function getRequiredHeaders(): ?array
    {
        return $this->requiredHeaders;
    }

    public function hasGroupPrefixes(): bool
    {
        return $this->hasValuesInArray($this->getGroupPrefixes());
    }

    public function hasRequiredHeaders(): bool
    {
        return $this->hasValuesInArray($this->getRequiredHeaders());
    }

    /**
     * @param null|mixed[] $array
     */
    private function hasValuesInArray(?array $array = null): bool
    {
        return \is_array($array) && \count($array) > 0;
    }
}
