<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony;

use EonX\EasyActivity\Bridge\Symfony\DependencyInjection\EasyActivityExtension;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriberInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyActivitySymfonyBundle extends Bundle
{
    public function boot(): void
    {
        parent::boot();
        /** @var \EonX\EasyDoctrine\Subscribers\EntityEventSubscriberInterface $eventSubscriber */
        $eventSubscriber = $this->container->get(EntityEventSubscriberInterface::class);

        /** @var array<string, array<string, mixed>> $subjects */
        $subjects = $this->container->getParameter(BridgeConstantsInterface::PARAM_SUBJECTS);
        foreach ($subjects as $subjectName => $meta) {
            $eventSubscriber->addAcceptableEntity($subjectName);
        }
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyActivityExtension();
    }
}
