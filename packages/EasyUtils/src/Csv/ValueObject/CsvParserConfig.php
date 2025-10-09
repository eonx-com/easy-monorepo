<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Csv\ValueObject;

final readonly class CsvParserConfig
{
    /**
     * @param string[]|null $requiredHeaders
     * @param string[]|null $groupPrefixes
     * @param callable[]|null $recordTransformers
     */
    public function __construct(
        private ?array $requiredHeaders = null,
        private ?array $groupPrefixes = null,
        private ?bool $ignoreEmptyRecords = null,
        private ?array $recordTransformers = null,
        private ?bool $ignoreEmptyRecordsWithRequiredHeaders = null,
    ) {
    }

    /**
     * @param string[]|null $requiredHeaders
     * @param string[]|null $groupPrefixes
     * @param callable[]|null $recordTransformers
     */
    public static function create(
        ?array $requiredHeaders = null,
        ?array $groupPrefixes = null,
        ?bool $ignoreEmptyRecords = null,
        ?array $recordTransformers = null,
        ?bool $ignoreEmptyRecordsWithRequiredHeaders = null,
    ): self {
        return new self(
            $requiredHeaders,
            $groupPrefixes,
            $ignoreEmptyRecords,
            $recordTransformers,
            $ignoreEmptyRecordsWithRequiredHeaders
        );
    }

    /**
     * @return string[]|null
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
     * @return string[]|null
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

    public function ignoreEmptyRecordsWithRequiredHeaders(): bool
    {
        return $this->ignoreEmptyRecordsWithRequiredHeaders ?? false;
    }

    private function hasValuesInArray(?array $array = null): bool
    {
        return \is_array($array) && \count($array) > 0;
    }
}
