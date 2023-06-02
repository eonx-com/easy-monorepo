<?php

declare(strict_types=1);

namespace EonX\EasySsm\Helpers;

use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Dotenv\Data\EnvData;
use EonX\EasySsm\Services\Parameters\Data\Diff;
use Nette\Utils\Strings;

final class Parameters
{
    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function applyDiff(Diff $diff, array $parameters): array
    {
        $parameters = $this->toIndexedByName($parameters);

        // Add new parameters
        foreach ($diff->getNew() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        // Override updated parameters
        foreach ($diff->getUpdated() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        // Remove deleted parameters
        foreach ($diff->getDeleted() as $parameter) {
            unset($parameters[$parameter->getName()]);
        }

        return $parameters;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @return \EonX\EasySsm\Services\Dotenv\Data\EnvData[]
     */
    public function convertToEnvs(array $parameters): array
    {
        $filter = static function ($parameter): bool {
            return $parameter instanceof SsmParameter;
        };

        $map = static function (SsmParameter $parameter): EnvData {
            return new EnvData($parameter->getName(), $parameter->getValue());
        };

        return \array_map($map, \array_filter($parameters, $filter));
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     */
    public function findParameter(string $name, array $parameters): ?SsmParameter
    {
        foreach ($parameters as $param) {
            if ($param->getName() === $name) {
                return $param;
            }
        }

        return null;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $remote
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $local
     *
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function merge(array $remote, array $local): array
    {
        $merge = $this->toIndexedByName($local);

        foreach ($remote as $param) {
            $merge[$param->getName()] = $param;
        }

        return $merge;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function removePathFromName(array $parameters, string $path): array
    {
        $array = [];

        // Make sure the path ends with a slash to prevent to export env vars starting with slash
        $path = Strings::endsWith($path, '/') ? $path : \sprintf('%s/', $path);

        foreach ($parameters as $parameter) {
            $array[] = new SsmParameter(
                \str_replace($path, '', $parameter->getName()),
                $parameter->getType(),
                $parameter->getValue(),
            );
        }

        return $array;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function toIndexedByName(array $parameters): array
    {
        $array = [];

        foreach ($parameters as $parameter) {
            $array[$parameter->getName()] = $parameter;
        }

        return $array;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @return mixed[]
     */
    public function toKeyObjectAsStrings(array $parameters): array
    {
        $array = [];

        foreach ($parameters as $parameter) {
            $array[$parameter->getName()] = \sprintf(
                'type: %s, value: %s',
                $parameter->getType(),
                $parameter->getValue(),
            );
        }

        return $array;
    }
}
