<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface SubscribeInfoInterface
{
    public function getJwt(): string;

    /**
     * @return string[]
     */
    public function getTopics(): array;

    public function getUrl(): string;
}
