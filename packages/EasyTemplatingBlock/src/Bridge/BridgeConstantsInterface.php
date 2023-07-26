<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_IS_DEBUG = 'easy_templating_block.is_debug';

    public const SERVICE_TEXT_BLOCK_RENDERER = 'easy_templating_block.text_renderer';

    public const SERVICE_TWIG_BLOCK_RENDERER = 'easy_templating_block.twig_renderer';

    public const TAG_TEMPLATING_BLOCK_PROVIDER = 'easy_templating_block.block_provider';

    public const TAG_TEMPLATING_BLOCK_RENDERER = 'easy_templating_block.block_renderer';
}
