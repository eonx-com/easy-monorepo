<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Parameters\Data;

final class Diff
{
    /**
     * @var \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    private $deleted;

    /**
     * @var \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    private $new;

    /**
     * @var \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    private $updated;

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $new
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $updated
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $deleted
     */
    public function __construct(array $new, array $updated, array $deleted)
    {
        $this->new = $new;
        $this->updated = $updated;
        $this->deleted = $deleted;
    }

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function getDeleted(): array
    {
        return $this->deleted;
    }

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function getNew(): array
    {
        return $this->new;
    }

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function getUpdated(): array
    {
        return $this->updated;
    }

    public function isDifferent(): bool
    {
        return empty($this->new) === false || empty($this->updated) === false || empty($this->deleted) === false;
    }
}
