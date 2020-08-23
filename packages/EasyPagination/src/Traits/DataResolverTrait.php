<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

trait DataResolverTrait
{
    /**
     * @param mixed $data
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    private function createStartSizeData(
        StartSizeConfigInterface $config,
        $data,
        $request
    ): StartSizeDataInterface {
        if ($request instanceof ServerRequestInterface) {
            @\trigger_error(\sprintf(
                'Passing $request as %s is deprecated since 2.4 and will not be supported in 3.0. Use %s instead.',
                ServerRequestInterface::class,
                Request::class
            ), \E_USER_DEPRECATED);
        }

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
