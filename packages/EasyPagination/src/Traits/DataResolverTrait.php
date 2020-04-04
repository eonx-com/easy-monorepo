<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use Psr\Http\Message\ServerRequestInterface;

trait DataResolverTrait
{
    /**
     * @param mixed $data
     */
    private function createStartSizeData(
        StartSizeConfigInterface $config,
        $data,
        ServerRequestInterface $request
    ): StartSizeDataInterface {
        if (\is_array($data) === false) {
            return new StartSizeData(
                $config->getStartDefault(),
                $config->getSizeDefault(),
                $config->getStartAttribute(),
                $config->getSizeAttribute()
            );
        }

        $start = empty($data[$config->getStartAttribute()])
            ? $config->getStartDefault()
            : (int)$data[$config->getStartAttribute()];

        $size = empty($data[$config->getSizeAttribute()])
            ? $config->getSizeDefault()
            : (int)$data[$config->getSizeAttribute()];

        return new StartSizeData(
            $start,
            $size,
            $config->getStartAttribute(),
            $config->getSizeAttribute(),
            (string)$request->getUri()
        );
    }
}
