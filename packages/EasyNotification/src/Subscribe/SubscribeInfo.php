<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Subscribe;

use EonX\EasyNotification\Interfaces\SubscribeInfoInterface;

final class SubscribeInfo implements SubscribeInfoInterface
{
    /**
     * @param string[] $topics
     */
    public function __construct(
        private string $jwt,
        private array $topics,
        private string $url,
    ) {
    }

    public static function fromArray(array $info): SubscribeInfoInterface
    {
        return new self($info['jwt'], $info['topics'], $info['url']);
    }

    public function getJwt(): string
    {
        return $this->jwt;
    }

    /**
     * @return string[]
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
