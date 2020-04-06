<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Filesystem;

use EonX\EasySsm\Helpers\Arr;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Filesystem\Exceptions\InvalidTagException;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

final class SsmParametersParser implements SsmParametersParserInterface
{
    /**
     * @var \EonX\EasySsm\Helpers\Arr
     */
    private $arr;

    public function __construct(Arr $arr)
    {
        $this->arr = $arr;
    }

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     *
     * @throws \EonX\EasySsm\Services\Filesystem\Exceptions\InvalidTagException
     */
    public function parseParameters(string $filename): array
    {
        $content = $this->arr->flatten(Yaml::parseFile($filename, Yaml::PARSE_CUSTOM_TAGS));
        $params = [];

        foreach ($content as $name => $value) {
            // Prefix param name with root /
            $name = \sprintf('/%s', $name);

            if ($value instanceof TaggedValue) {
                if ($value->getTag() !== 'secure') {
                    throw new InvalidTagException(\sprintf('Expected tag "secure", "%s" given', $value->getTag()));
                }

                $params[] = new SsmParameter($name, 'SecureString', $this->getValue($value->getValue()));

                continue;
            }

            $params[] = new SsmParameter($name, 'String', $this->getValue($value));
        }

        return $params;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function getValue($value)
    {
        if (\is_string($value) === false) {
            return $value;
        }

        return \trim($value);
    }
}
