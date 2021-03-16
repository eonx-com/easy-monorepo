<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Helpers;

use Nette\Utils\Json;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
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
