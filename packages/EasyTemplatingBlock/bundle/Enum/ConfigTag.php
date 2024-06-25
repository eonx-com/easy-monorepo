<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bundle\Enum;

enum ConfigTag: string
{
    case TemplatingBlockProvider = 'easy_templating_block.block_provider';

    case TemplatingBlockRenderer = 'easy_templating_block.block_renderer';
}
