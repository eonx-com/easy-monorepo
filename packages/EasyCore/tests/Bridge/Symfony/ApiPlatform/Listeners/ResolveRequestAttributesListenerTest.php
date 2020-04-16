<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\Listeners;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Event\DataPersisterResolvedEvent;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Exception\RequestAttributesSetterNotFoundException;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Listeners\ResolveRequestAttributesListener;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\RequestAttributesAwareStub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ResolveRequestAttributesListenerTest extends AbstractSymfonyTestCase
{
    public function testRequestIsNull(): void
    {
        $requestStack = new RequestStack();

        $listener = new ResolveRequestAttributesListener($requestStack);
        $dataPersister = new RequestAttributesAwareStub();

        $listener->__invoke(new DataPersisterResolvedEvent($dataPersister));

        self::assertNull($dataPersister->getRequestAttributes());
    }

    public function testSetRequestAttributesSuccessfully(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request([], [], ['param1' => 'value1']));

        $listener = new ResolveRequestAttributesListener($requestStack);
        $dataPersister = new RequestAttributesAwareStub();

        $listener->__invoke(new DataPersisterResolvedEvent($dataPersister));

        self::assertEquals(['value1'], $dataPersister->getRequestAttributes());
    }

    public function testThrowSetterNotFoundException(): void
    {
        $this->expectException(RequestAttributesSetterNotFoundException::class);

        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $listener = new ResolveRequestAttributesListener($requestStack);

        $listener->__invoke(new DataPersisterResolvedEvent(new RequestAttributesAwareStub('invalid')));
    }
}
