<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

final class CsvParserConfig implements CsvParserConfigInterface
{
    /**
     * @param null|string[] $requiredHeaders
     * @param null|string[] $groupPrefixes
     * @param null|callable[] $recordTransformers
     */
    public function __construct(
        private readonly ?array $requiredHeaders = null,
        private readonly ?array $groupPrefixes = null,
        private readonly ?bool $ignoreEmptyRecords = null,
        private readonly ?array $recordTransformers = null
    ) {
    }

    /**
     * @param null|string[] $requiredHeaders
     * @param null|string[] $groupPrefixes
     * @param null|callable[] $recordTransformers
     */
    public static function create(
        ?array $requiredHeaders = null,
        ?array $groupPrefixes = null,
        ?bool $ignoreEmptyRecords = null,
        ?array $recordTransformers = null
    ): self {
        return new self($requiredHeaders, $groupPrefixes, $ignoreEmptyRecords, $recordTransformers);
    }

    /**
     * @return null|string[]
     */
    public function getGroupPrefixes(): ?array
    {
        return $this->groupPrefixes;
    }

    /**
     * @return callable[]
     */
    public function getRecordTransformers(): array
    {
        return $this->recordTransformers ?? [];
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

    public function ignoreEmptyRecords(): bool
    {
        return $this->ignoreEmptyRecords ?? false;
    }

    /**
     * @param null|mixed[] $array
     */
    private function hasValuesInArray(?array $array = null): bool
    {
        return \is_array($array) && \count($array) > 0;
    }
}
