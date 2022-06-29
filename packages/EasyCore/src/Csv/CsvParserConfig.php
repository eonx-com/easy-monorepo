<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

/**
 * @deprecated since 4.1, will be removed in 5.0. Use Eonx\EasyUtils\Csv\CsvParserConfig.
 */
final class CsvParserConfig implements CsvParserConfigInterface
{
    /**
     * @var null|string[]
     */
    private $groupPrefixes;

    /**
     * @var null|string[]
     */
    private $requiredHeaders;

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
}
