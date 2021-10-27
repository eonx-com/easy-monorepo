<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_IS_DEBUG = 'easy_templating_block.is_debug';

    /**
     * @var string
     */
    public const TAG_TEMPLATING_BLOCK_PROVIDER = 'easy_templating_block.block_provider';

    /**
     * @var string
     */
    public const TAG_TEMPLATING_BLOCK_RENDERER = 'easy_templating_block.block_renderer';

    /**
     * @var string
     */
    public const SERVICE_TWIG_BLOCK_RENDERER = 'easy_templating_block.twig_renderer';
}
