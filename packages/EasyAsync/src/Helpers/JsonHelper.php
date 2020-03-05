<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Helpers;

use Nette\Utils\Json;

final class JsonHelper
{
    /**
     * @return null|mixed[]
     *
     * @throws \Nette\Utils\JsonException
     */
    public static function decode(?string $json = null): ?array
    {
        return $json ? Json::decode($json, Json::FORCE_ARRAY) : null;
    }

    /**
     * @param null|mixed[] $data
     *
     * @throws \Nette\Utils\JsonException
     */
    public static function encode(?array $data = null): ?string
    {
        return $data ? Json::encode($data) : null;
    }
}
