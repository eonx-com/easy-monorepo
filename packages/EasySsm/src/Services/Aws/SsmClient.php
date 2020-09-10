<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws;

use Aws\Ssm\SsmClient as BaseSsmClient;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Parameters\Data\Diff;

final class SsmClient implements SsmClientInterface
{
    /**
     * @var \Aws\Ssm\SsmClient
     */
    private $ssm;

    public function __construct(BaseSsmClient $ssm)
    {
        $this->ssm = $ssm;
    }

    public function applyDiff(Diff $diff): void
    {
        // Skip if no difference
        if ($diff->isDifferent() === false) {
            return;
        }

        // Add new parameters
        foreach ($diff->getNew() as $parameter) {
            $this->ssm->putParameter($parameter->toArray());
        }

        // Overwrite updated parameters
        foreach ($diff->getUpdated() as $parameter) {
            $array = $parameter->toArray();
            $array['Overwrite'] = true;

            $this->ssm->putParameter($array);
        }

        // Remove deleted parameters
        foreach ($diff->getDeleted() as $parameter) {
            $this->ssm->deleteParameter(['Name' => $parameter->getName()]);
        }
    }

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function getAllParameters(?string $path = null): array
    {
        $results = $this->ssm->getPaginator('GetParametersByPath', [
            'Path' => $path ?? '/',
            'Recursive' => true,
            'WithDecryption' => true,
        ]);

        $params = [];

        foreach ($results as $result) {
            foreach ($result['Parameters'] ?? [] as $param) {
                $params[] = new SsmParameter($param['Name'], $param['Type'], \trim($param['Value']));
            }
        }

        return $params;
    }
}
