<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const SERVICE_CONNECTION = 'easy_lock.connection';

    /**
     * @var string
     */
    public const SERVICE_STORE = 'easy_lock.store';
}
