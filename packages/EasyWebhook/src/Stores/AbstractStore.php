<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use Nette\Utils\Json;

abstract class AbstractStore
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    protected $random;

    public function __construct(RandomGeneratorInterface $random)
    {
        $this->random = $random;
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
        return \array_map(static function ($value) {
            if (\is_array($value)) {
                return Json::encode($value);
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format(StoreInterface::DATETIME_FORMAT);
            }

            return $value;
        }, $data);
    }
}
