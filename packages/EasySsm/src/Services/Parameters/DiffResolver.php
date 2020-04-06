<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Parameters;

use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Parameters\Data\Diff;

final class DiffResolver implements DiffResolverInterface
{
    /**
     * @var \EonX\EasySsm\Helpers\Parameters
     */
    private $parametersHelper;

    public function __construct(Parameters $parametersHelper)
    {
        $this->parametersHelper = $parametersHelper;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $remote
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $local
     */
    public function diff(array $remote, array $local): Diff
    {
        $new = [];
        $updated = [];
        $deleted = [];

        // Check for new and updates
        foreach ($local as $param) {
            $remoteParam = $this->parametersHelper->findParameter($param->getName(), $remote);

            // If remote parameter doesn't exist, it's a new one
            if ($remoteParam === null) {
                $new[] = $param;

                continue;
            }

            // If values are different then it's an update
            if ($param->getValue() !== $remoteParam->getValue() || $param->getType() !== $remoteParam->getType()) {
                $updated[] = $param;

                continue;
            }
        }

        // Check for deletes
        foreach ($remote as $param) {
            $localParam = $this->parametersHelper->findParameter($param->getName(), $local);

            if ($localParam === null) {
                $deleted[] = $param;
            }
        }

        return new Diff($new, $updated, $deleted);
    }
}
