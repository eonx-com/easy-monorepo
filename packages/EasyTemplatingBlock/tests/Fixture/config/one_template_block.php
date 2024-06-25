<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTemplatingBlock\Bundle\Enum\ConfigTag;
use EonX\EasyTemplatingBlock\Common\Provider\ArrayTemplatingBlockProvider;
use EonX\EasyTemplatingBlock\Common\ValueObject\TemplateBlock;
use Symfony\Config\EasyTemplatingBlockConfig;

return static function (
    EasyTemplatingBlockConfig $easyTemplatingBlockConfig,
    ContainerConfigurator $containerConfigurator,
): void {
    $easyTemplatingBlockConfig->isDebug(false);

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
