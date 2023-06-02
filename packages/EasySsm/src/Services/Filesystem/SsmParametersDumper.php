<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Filesystem;

use EonX\EasySsm\Helpers\Arr;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

final class SsmParametersDumper implements SsmParametersDumperInterface
{
    /**
     * @var \EonX\EasySsm\Helpers\Arr
     */
    private $arr;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    public function __construct(Arr $arr, Filesystem $filesystem)
    {
        $this->arr = $arr;
        $this->filesystem = $filesystem;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     */
    public function dumpParameters(string $filename, array $parameters): void
    {
        $this->filesystem->dumpFile($filename, Yaml::dump(
            $this->unflatten($parameters),
            \PHP_INT_MAX,
            4,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
        ));
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @return mixed[]
     */
    private function unflatten(array $parameters): array
    {
        $array = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $value = $parameter->getValue();

            $array[$name] = $parameter->getType() !== 'SecureString' ? $value : new TaggedValue('secure', $value);
        }

        \ksort($array);

        return $this->arr->unflatten($array);
    }
}
