<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\Stores\DataCleanerInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use Nette\Utils\Json;

abstract class AbstractStore
{
    public function __construct(
        protected RandomGeneratorInterface $random,
        private DataCleanerInterface $dataCleaner,
    ) {
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     *
     * @throws \Nette\Utils\JsonException
     */
    protected function formatData(array $data): array
    {
        $data = \array_map(static function ($value) {
            if (\is_array($value)) {
                return Json::encode($value);
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format(StoreInterface::DATETIME_FORMAT);
            }

            return $value;
        }, $data);

        return $this->dataCleaner->cleanUpData($data);
    }
}
