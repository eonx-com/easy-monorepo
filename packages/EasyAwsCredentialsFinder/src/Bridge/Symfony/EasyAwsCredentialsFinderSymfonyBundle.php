<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Bridge\Symfony;

use EonX\EasyAwsCredentialsFinder\Bridge\Symfony\DependencyInjection\EasyAwsCredentialsFinderExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;

final class EasyAwsCredentialsFinderSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyAwsCredentialsFinderExtension();
    }
}
