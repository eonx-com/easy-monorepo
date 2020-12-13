<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\ApiFormats\Interfaces;

interface FormattedApiResponseInterface
{
    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @return string[]
     */
    public function getHeaders(): array;

    public function getStatusCode(): int;
}
