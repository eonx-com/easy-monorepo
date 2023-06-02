<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Helpers;

use EonX\EasyBankFiles\Exceptions\ImpossibleException;

/**
 * A helper class that works to mitigate XML-related issues.
 */
final class XmlFailureMitigation
{
    /**
     * Attempts to work around common data inconsistencies that the banks find acceptable (for some odd reason).
     *
     * @param string $content The XML content.
     *
     * @throws \EonX\EasyBankFiles\Exceptions\ImpossibleException
     */
    public static function tryMitigateParseFailures(string $content): string
    {
        // Split the content in to individual lines
        $lines = \explode(\PHP_EOL, \trim($content));

        // Iterate through each line from the content string
        foreach ($lines as &$line) {
            // Find any matching node element name and content in the line
            $result = \preg_match_all('/<([A-Za-z0-9-_]+)?(?:[^\>]+)?>(.*)<\/\1>/', $line, $matches, \PREG_SET_ORDER);
            if ($result === false || $result === 0) {
                continue;
            }

            // Begin to iterate through each node key and value
            // We assume here that there *could* be more than one XML element in a given line.
            foreach ($matches as $match) {
                // If the match does not contain three elements, skip
                if (\count($match) !== 3) {
                    // @codeCoverageIgnoreStart
                    // Sanity check only and unable to be tested.
                    throw new ImpossibleException(\sprintf(
                        'Regular expression match result should have 3 children, %d found.',
                        \count($match),
                    ));
                    // @codeCoverageIgnoreEnd
                }

                $matched = $match[0];
                $value = $match[2];

                // Check if the value contains any HTML characters which would cause XML parsing issues
                if (\preg_match('//', $value) > 0) {
                    $value = \htmlentities($value);
                }

                // If the value has not been modified, continue
                if ($value === $match[2]) {
                    continue;
                }

                // Update the line with the new value
                $replacement = \str_replace($match[2], $value, $matched);
                $line = \str_replace($matched, $replacement, $line);
            }
        }

        return \implode(\PHP_EOL, $lines);
    }
}
