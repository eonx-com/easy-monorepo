<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Interfaces;

interface TagsInterface
{
    /**
     * @var string
     */
    public const DOCTRINE_AUTOCONFIG_ENTITY_EVENT_LISTENER = 'easy_core.doctrine_autoconfig_entity_event_listener';

    /**
     * @var string
     */
    public const DOCTRINE_AUTOCONFIG_EVENT_LISTENER = 'easy_core.doctrine_autoconfig_event_listener';
}
