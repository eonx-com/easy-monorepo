<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge;

interface TagsInterface
{
    /**
     * @var string
     */
    public const TAG_CONTEXT_CONFIGURATOR = 'easy_security.context_configurator';

    /**
     * @var string
     */
    public const TAG_CONTEXT_MODIFIER = 'easy_security.context_modifier';
}
