<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

use EonX\EasyUtils\Csv\Exceptions\MissingRequiredHeadersException;
use EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException;

final class CsvWithHeadersParser implements CsvWithHeadersParserInterface
{
    /**
     * @return iterable<mixed>
     *
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingRequiredHeadersException
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException
     */
    public function parse(
        CsvContentsProviderInterface $contentsProvider,
        ?CsvParserConfigInterface $config = null,
    ): iterable {
        $config = $config ?? CsvParserConfig::create();
        $index = 0;
        $headers = [];

        foreach ($contentsProvider->getContents() as $row) {
            $index++;
            $record = [];

            // First line is headers
            if ($index === 1) {
                $headers = $this->resolveHeaders($row, $config);

                continue;
            }

            foreach ($row as $key => $value) {
                // Accept only value for known headers and no empty string
                if (isset($headers[$key]) && $value !== '') {
                    $record[$headers[$key]] = \trim($value);
                }
            }

            \ksort($record);

            $this->validateMissingValues($record, $index, $config);

            // Ignore empty records after validation so missing values are picked up
            if ($config->ignoreEmptyRecords() && \count($record) < 1) {
                continue;
            }

            $record = $this->handlePrefixes($record, $config);

            foreach ($config->getRecordTransformers() as $recordTransformer) {
                $record = \call_user_func($recordTransformer, $record);
            }

            yield $record;
        }
    }

    /**
     * @param mixed[] $record
     *
     * @return mixed[]
     */
    private function handlePrefixes(array $record, CsvParserConfigInterface $config): array
    {
        if ($config->hasGroupPrefixes() === false) {
            return $record;
        }

        $newRecord = [];

        // Loop through existing record
        foreach ($record as $key => $value) {
            $keyHadPrefix = false;

            // For each prefix
            foreach ($config->getGroupPrefixes() ?? [] as $prefix) {
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
     * @param mixed[] $headers
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingRequiredHeadersException
     */
    private function resolveHeaders(array $headers, CsvParserConfigInterface $config): array
    {
        // Sanitize given headers first
        $headers = \array_map(static function (string $header): string {
            $header = (string)\iconv('UTF-8', 'ISO-8859-1//IGNORE', $header);
            $header = (string)\iconv('ISO-8859-1', 'UTF-8', $header);

            return \trim($header);
        }, $headers);

        if ($config->hasRequiredHeaders()) {
            $missingHeaders = \array_diff($config->getRequiredHeaders() ?? [], $headers);

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
     * @throws \EonX\EasyUtils\Csv\Exceptions\MissingValueForRequiredHeadersException
     */
    private function validateMissingValues(array $record, int $index, CsvParserConfigInterface $config): void
    {
        if ($config->hasRequiredHeaders() === false) {
            return;
        }

        $missingValues = \array_diff($config->getRequiredHeaders() ?? [], \array_keys($record));

        if (\count($missingValues) > 0) {
            throw new MissingValueForRequiredHeadersException(\sprintf(
                'Missing values for required headers ["%s"] for record %d',
                \implode('","', $missingValues),
                $index
            ));
        }
    }
}
