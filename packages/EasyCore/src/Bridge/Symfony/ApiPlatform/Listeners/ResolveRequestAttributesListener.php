<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Listeners;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Event\DataPersisterResolvedEvent;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Traits\RequestAttributesAwareTrait;
use EonX\EasyCore\Bridge\Symfony\Event\KernelEventListenerTrait;
use EonX\EasyCore\Bridge\Symfony\Interfaces\EventListenerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

final class ResolveRequestAttributesListener implements EventListenerInterface
{
    use KernelEventListenerTrait;

    /**
     * @var \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface
     */
    private $argumentResolver;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(RequestStack $requestStack, ?ArgumentResolverInterface $argumentResolver = null)
    {
        $this->argumentResolver = $argumentResolver ?? new ArgumentResolver();
        $this->request = $requestStack->getMasterRequest();
    }

    public function __invoke(DataPersisterResolvedEvent $event): void
    {
        if ($this->request === null) {
            return;
        }

        $dataPersister = $event->getDataPersister();

        if (\in_array(RequestAttributesAwareTrait::class, \class_uses($dataPersister), true) === false) {
            return;
        }

        /** @var \EonX\EasyCore\Bridge\Symfony\ApiPlatform\Traits\RequestAttributesAwareTrait $dataPersister */

        $args = $this->argumentResolver->getArguments($this->request, [$dataPersister, 'setRequestAttributes']);

        $dataPersister->setRequestAttributes(...$args);
    }
}
