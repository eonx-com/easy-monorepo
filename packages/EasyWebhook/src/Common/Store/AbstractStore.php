<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Store;

use BackedEnum;
use DateTimeInterface;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use Nette\Utils\Json;

abstract class AbstractStore
{
    public function __construct(
        protected RandomGeneratorInterface $random,
        private readonly DataCleanerInterface $dataCleaner,
    ) {
    }

    /**
     * @throws \Nette\Utils\JsonException
     */
    protected function formatData(array $data): array
    {
        $data = \array_map(static function ($value) {
            if (\is_array($value)) {
                return Json::encode($value);
            }

            if ($value instanceof DateTimeInterface) {
                return $value->format(StoreInterface::DATETIME_FORMAT);
            }

            if ($value instanceof BackedEnum) {
                return $value->value;
            }

            return $value;
        }, $data);

        return $this->dataCleaner->cleanUpData($data);
    }
}
