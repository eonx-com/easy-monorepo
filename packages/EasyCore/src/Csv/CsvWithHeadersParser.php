<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

use EonX\EasyCore\Csv\Exceptions\MissingRequiredHeadersException;
use EonX\EasyCore\Csv\Exceptions\MissingValueForRequiredHeadersException;

final class CsvWithHeadersParser implements CsvWithHeadersParserInterface
{
    /**
     * @var \EonX\EasyCore\Csv\CsvParserConfigInterface
     */
    private $config;

    /**
     * @var bool
     */
    private $hasGroupPrefixes;

    /**
     * @var bool
     */
    private $hasRequiredHeaders;

    public function __construct(CsvParserConfigInterface $config)
    {
        $this->config = $config;
        $this->hasGroupPrefixes = $this->hasValuesInArray($config->getGroupPrefixes());
        $this->hasRequiredHeaders = $this->hasValuesInArray($config->getRequiredHeaders());
    }

    /**
     * @return iterable<mixed>
     *
     * @throws \EonX\EasyCore\Csv\Exceptions\MissingRequiredHeadersException
     * @throws \EonX\EasyCore\Csv\Exceptions\MissingValueForRequiredHeadersException
     */
    public function parse(CsvContentsProviderInterface $contentsProvider): iterable
    {
        $index = 0;
        $headers = [];

        foreach ($contentsProvider->getContents() as $row) {
            $index++;
            $record = [];

            // First line is headers
            if ($index === 1) {
                $headers = $this->resolveHeaders($row);

                continue;
            }

            foreach ($row as $key => $value) {
                // Accept only value for known headers and no empty string
                if (isset($headers[$key]) && $value !== '') {
                    $record[$headers[$key]] = \trim($value);
                }
            }

            \ksort($record);
            $this->validateMissingValues($record, $index);

            yield $this->handlePrefixes($record);
        }
    }

    /**
     * @param mixed[] $record
     *
     * @return mixed[]
     */
    private function handlePrefixes(array $record): array
    {
        if ($this->hasGroupPrefixes === false) {
            return $record;
        }

        $newRecord = [];

        // Loop through existing record
        foreach ($record as $key => $value) {
            $keyHadPrefix = false;

            // For each prefix
            foreach ($this->config->getGroupPrefixes() ?? [] as $prefix) {
                // User give prefix without dot, add it
                $prefixWithDot = \sprintf('%s.', $prefix);
                // Extract prefix from current key
                $extractPrefixWithDot = \substr($key, 0, \strlen($prefixWithDot));

                // If current key, doesn't start with prefix, skip
                if ($extractPrefixWithDot !== $prefixWithDot) {
                    continue;
                }

                // Mark key as having prefix, so we don't add it to the generated record later
                $keyHadPrefix = true;

                // Handle first prefixed key, set array
                if (isset($newRecord[$prefix]) === false) {
                    $newRecord[$prefix] = [];
                }

                // Add key without prefix into the prefix array
                $newRecord[$prefix][\str_replace($prefixWithDot, '', $key)] = $value;

                // If key prefixed with current prefix, no need to keep looping through prefixes
                break;
            }

            // If key wasn't prefixed, add it to new record to preserve not prefixed keys
            if ($keyHadPrefix === false) {
                $newRecord[$key] = $value;
            }
        }

        // Replace record with new one
        return $newRecord;
    }

    /**
     * @param null|mixed[] $array
     */
    private function hasValuesInArray(?array $array = null): bool
    {
        return \is_array($array) && \count($array) > 0;
    }

    /**
     * @param mixed[] $headers
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyCore\Csv\Exceptions\MissingRequiredHeadersException
     */
    private function resolveHeaders(array $headers): array
    {
        // Sanitize given headers first
        $headers = \array_map(static function (string $header): string {
            $header = \iconv("UTF-8","ISO-8859-1//IGNORE",$header);
            $header = \iconv("ISO-8859-1","UTF-8",$header);
            $header = \trim($header);

            return $header;
        }, $headers);

        if ($this->hasRequiredHeaders) {
            $missingHeaders = \array_diff($this->config->getRequiredHeaders() ?? [], $headers);

            if (\count($missingHeaders) > 0) {
                throw new MissingRequiredHeadersException(\sprintf(
                    'Missing required headers ["%s"], given headers: ["%s"]',
                    \implode('","', $missingHeaders),
                    \implode('","', $headers)
                ));
            }
        }

        return $headers;
    }

    /**
     * @param mixed[] $record
     *
     * @throws \EonX\EasyCore\Csv\Exceptions\MissingValueForRequiredHeadersException
     */
    private function validateMissingValues(array $record, int $index): void
    {
        if ($this->hasRequiredHeaders === false) {
            return;
        }

        $missingValues = \array_diff($this->config->getRequiredHeaders() ?? [], \array_keys($record));

        if (\count($missingValues) > 0) {
            throw new MissingValueForRequiredHeadersException(\sprintf(
                'Missing values for required headers ["%s"] for record %d',
                \implode('","', $missingValues),
                $index
            ));
        }
    }
}
