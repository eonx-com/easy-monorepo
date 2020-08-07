<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Subscribe;

use EonX\EasyNotification\Interfaces\SubscribeInfoInterface;

final class SubscribeInfo implements SubscribeInfoInterface
{
    /**
     * @var string
     */
    private $jwt;

    /**
     * @var string[]
     */
    private $topics;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string[] $topics
     */
    public function __construct(string $jwt, array $topics, string $url)
    {
        $this->jwt = $jwt;
        $this->topics = $topics;
        $this->url = $url;
    }

    /**
     * @var mixed[] $info
     */
    public static function fromArray(array $info): SubscribeInfoInterface
    {
        return new static($info['jwt'], $info['topics'], $info['url']);
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
