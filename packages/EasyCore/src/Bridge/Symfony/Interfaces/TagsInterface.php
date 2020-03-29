<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Interfaces;

interface TagsInterface
{
    /**
     * @var string
     */
    public const EVENT_LISTENER_AUTO_CONFIG = 'easy_core.event_listener_auto_config';

    /**
     * @var string
     */
    public const SIMPLE_DATA_PERSISTER_AUTO_CONFIG = 'easy_core.simple_data_persister_auto_config';
}
