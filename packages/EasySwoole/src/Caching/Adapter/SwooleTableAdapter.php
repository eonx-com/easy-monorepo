<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Caching\Adapter;

use EonX\EasySwoole\Caching\Helper\CacheTableHelper;
use InvalidArgumentException;
use OpenSwoole\Table as OpenSwooleTable;
use Swoole\Table as SwooleTable;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;
use Throwable;
use UnexpectedValueException;

final class SwooleTableAdapter extends AbstractAdapter
{
    public function __construct(
        private readonly string $tableName,
        ?int $defaultLifetime = null,
        private readonly MarshallerInterface $marshaller = new DefaultMarshaller(),
    ) {
        if (CacheTableHelper::exists($this->tableName) === false) {
            throw new InvalidArgumentException(\sprintf(
                'SwooleTable "%s" does not exist, make sure you have set it in your easy_swoole config',
                $this->tableName
            ));
        }

        parent::__construct('', $defaultLifetime ?? 0);
    }

    protected function doClear(string $namespace): bool
    {
        $table = $this->getSwooleTable();

        foreach ($table as $id => $value) {
            $table->del($id);
        }

        return true;
    }

    protected function doDelete(array $ids): bool
    {
        $table = $this->getSwooleTable();

        foreach ($ids as $id) {
            if ($table->exists($id)) {
                $table->del($id);
            }
        }

        return true;
    }

    /**
     * @param string[] $ids
     *
     * @throws \Exception
     */
    protected function doFetch(array $ids): array
    {
        $table = $this->getSwooleTable();
        $values = [];
        $now = \time();

        foreach ($ids as $id) {
            if ($table->exists($id) === false) {
                continue;
            }

            $item = $table->get($id);

            if ($now >= $item[CacheTableHelper::COLUMN_EXPIRY]) {
                $table->del($id);

                continue;
            }

            $values[$id] = $this->marshaller->unmarshall($item[CacheTableHelper::COLUMN_VALUE]);
        }

        return $values;
    }

    protected function doHave(string $id): bool
    {
        return $this->getSwooleTable()
            ->exists($id);
    }

    protected function doSave(array $values, int $lifetime): array|bool
    {
        $table = $this->getSwooleTable();
        $expiresAt = $lifetime !== 0 ? (\time() + $lifetime) : 0;
        $values = $this->marshaller->marshall($values, $failed);

        foreach ($values as $id => $value) {
            try {
                $table->set($id, [
                    CacheTableHelper::COLUMN_EXPIRY => $expiresAt,
                    CacheTableHelper::COLUMN_VALUE => $value,
                ]);
            } catch (Throwable) {
                $failed[] = $id;
            }
        }

        if (\count($failed) > 0) {
            throw new CacheException(\sprintf('Could not save ids %s', \implode(', ', $failed)));
        }

        return $failed;
    }

    private function getSwooleTable(): SwooleTable|OpenSwooleTable
    {
        return CacheTableHelper::get($this->tableName)
            ?? throw new UnexpectedValueException(\sprintf('Cache table "%s" does not exist.', $this->tableName));
    }
}
