<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Stubs;

use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Aws\SsmClientInterface;
use EonX\EasySsm\Services\Parameters\Data\Diff;

final class SsmClientStub implements SsmClientInterface
{
    /**
     * @var \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    private $parameters;

    /**
     * @param null|\EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     */
    public function __construct(?array $parameters = null)
    {
        $this->parameters = $this->formatParameters($parameters ?? []);
    }

    public function applyDiff(Diff $diff): void
    {
    }

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function getAllParameters(?string $path = null): array
    {
        return $this->parameters;
    }

    /**
     * @param mixed[]|\EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    private function formatParameters(array $parameters): array
    {
        $map = static function ($param): SsmParameter {
            if ($param instanceof SsmParameter) {
                return $param;
            }

            return new SsmParameter($param['name'], $param['type'], $param['value']);
        };

        return \array_map($map, $parameters);
    }
}
