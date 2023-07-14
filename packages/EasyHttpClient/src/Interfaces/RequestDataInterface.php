<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Interfaces;

use DateTimeInterface;

interface RequestDataInterface
{
    public function getMethod(): string;

    /**
     * @return mixed[]
     */
    public function getOptions(): array;

    public function getSentAt(): DateTimeInterface;

    public function getUrl(): string;

    /**
     * @param mixed[] $options
     */
    public function setOptions(array $options): self;
}
