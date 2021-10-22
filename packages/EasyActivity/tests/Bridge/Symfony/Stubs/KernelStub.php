<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyActivity\Bridge\Symfony\EasyActivitySymfonyBundle;
use EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonyActivitySubjectDataSerializer;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataSerializerInterface;
use EonX\EasyDoctrine\Bridge\BridgeConstantsInterface;
use EonX\EasyDoctrine\Bridge\Symfony\EasyDoctrineSymfonyBundle;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriberInterface;
use EonX\EasyEventDispatcher\Bridge\Symfony\EventDispatcher;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyWebhook\Tests\Bridge\Symfony\Stubs\MessageBusStub;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as SymfonyNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private $configs;

    /**
     * @param null|string[] $configs
     */
    public function __construct(?array $configs = null)
    {
        $this->configs = $configs ?? [];

        parent::__construct('test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(Connection::class, new Definition(Connection::class));
        $container->setDefinition(ManagerRegistry::class, new Definition(ManagerRegistry::class));
        $container->setDefinition(EntityManagerInterface::class, new Definition(EntityManager::class));
        $container->setDefinition('doctrine.orm.default_entity_manager', new Definition(EntityManager::class));
        $container->setDefinition(SymfonyNormalizerInterface::class, new Definition(ObjectNormalizer::class));
        $container->setDefinition(
            ActivitySubjectDataSerializerInterface::class,
            new Definition(SymfonyActivitySubjectDataSerializer::class)
        );
        $deferredEntityDefinition = new Definition(DeferredEntityEventDispatcher::class, [
            new Definition(EventDispatcher::class, [new Definition(SymfonyEventDispatcher::class)]),
        ]);
        $container->setDefinition(DeferredEntityEventDispatcherInterface::class, $deferredEntityDefinition);
        $container->setDefinition(RandomGeneratorInterface::class, new Definition(RandomGenerator::class));
        $container->setDefinition(LoggerInterface::class, new Definition(NullLogger::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusStub::class));
        $container->setDefinition(
            EntityEventSubscriberInterface::class,
            new Definition(
                EntityEventSubscriber::class,
                [$deferredEntityDefinition, '%' . BridgeConstantsInterface::PARAM_DEFERRED_DISPATCHER_ENTITIES . '%']
            )
        );

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyDoctrineSymfonyBundle();
        yield new EasyActivitySymfonyBundle();
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
