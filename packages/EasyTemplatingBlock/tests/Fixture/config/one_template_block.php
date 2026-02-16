<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTemplatingBlock\Bundle\Enum\ConfigTag;
use EonX\EasyTemplatingBlock\Common\Provider\ArrayTemplatingBlockProvider;
use EonX\EasyTemplatingBlock\Common\ValueObject\TemplateBlock;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_templating_block', [
        'is_debug' => false,
    ]);

    $services = $containerConfigurator->services();

    $services->set('one_template_block', TemplateBlock::class)
        ->arg('$name', 'one_template_block')
        ->call('setTemplateName', ['my_block.html.twig']);

    $services->set('one_template_block_provider', ArrayTemplatingBlockProvider::class)
        ->arg('$blocks', [
            'my-event' => [service('one_template_block')],
        ])
        ->tag(ConfigTag::TemplatingBlockProvider->value);
};
