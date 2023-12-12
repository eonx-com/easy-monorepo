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
use EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\Bridge\Symfony\EasyDoctrineSymfonyBundle;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Listeners\EntityOnFlushEventListener;
use EonX\EasyDoctrine\Listeners\EntityPostFlushEventListener;
use EonX\EasyDoctrine\Utils\ObjectCopier;
use EonX\EasyEventDispatcher\Bridge\Symfony\EventDispatcher;
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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as SymfonyNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Factory\UuidFactory;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private array $configs;

    /**
     * @param string[]|null $configs
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
        $container->setDefinition(
            ActivitySubjectDataSerializerInterface::class,
            new Definition(SymfonyActivitySubjectDataSerializer::class)
        );
        $deferredEntityDefinition = new Definition(DeferredEntityEventDispatcher::class, [
            new Definition(EventDispatcher::class, [new Definition(SymfonyEventDispatcher::class)]),
            (new Definition(ObjectCopier::class))
                ->setFactory([ObjectCopierFactory::class, 'create']),
        ]);
        $container->setDefinition(DeferredEntityEventDispatcherInterface::class, $deferredEntityDefinition);
        $container->setDefinition(UuidFactory::class, new Definition(UuidFactory::class));
        $container->setDefinition(LoggerInterface::class, new Definition(NullLogger::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusStub::class));
        $container->setDefinition(
            EntityOnFlushEventListener::class,
            new Definition(
                EntityOnFlushEventListener::class,
                [$deferredEntityDefinition, '%' . BridgeConstantsInterface::PARAM_DEFERRED_DISPATCHER_ENTITIES . '%']
            )
        );
        $container->setDefinition(
            EntityPostFlushEventListener::class,
            new Definition(EntityPostFlushEventListener::class, [$deferredEntityDefinition])
        );
        $objectNormalizerDefinition = new Definition(ObjectNormalizer::class);
        $dateTimeNormalizerDefinition = new Definition(DateTimeNormalizer::class);
        $jsonEncoderDefinition = new Definition(JsonEncoder::class);
        $container->setDefinition(SymfonyNormalizerInterface::class, $objectNormalizerDefinition);
        $container->setDefinition(
            SerializerInterface::class,
            new Definition(
                Serializer::class,
                [[$dateTimeNormalizerDefinition, $objectNormalizerDefinition], [$jsonEncoderDefinition]]
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
