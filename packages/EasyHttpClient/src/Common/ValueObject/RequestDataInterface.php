<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\ValueObject;

use DateTimeInterface;

interface RequestDataInterface
{
    public function getMethod(): string;

    public function getOptions(): array;

    public function getSentAt(): DateTimeInterface;

    public function getUrl(): string;

    public function setOptions(array $options): self;
}
