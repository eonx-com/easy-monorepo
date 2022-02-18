<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface MessageInterface
{
    /**
     * @var string
     */
    public const TYPE_FLASH = 'flash';

    /**
     * @var string
     */
    public const TYPE_PUSH = 'push';

    /**
     * @var string
     */
    public const TYPE_REAL_TIME = 'real_time';

    /**
     * @var string
     */
    public const TYPE_SLACK = 'slack';

    public function getBody(): string;

    public function getType(): string;
}
