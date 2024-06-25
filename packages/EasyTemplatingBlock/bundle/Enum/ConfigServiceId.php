<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bundle\Enum;

enum ConfigServiceId: string
{
    case TextBlockRenderer = 'easy_templating_block.text_renderer';

    case TwigBlockRenderer = 'easy_templating_block.twig_renderer';
}
