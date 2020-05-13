<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\DataPersister;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\DoctrineOrmDataPersister;
use EonX\EasyCore\Tests\AbstractTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\ContextAwareDataPersisterStub;

final class DoctrineOrmDataPersisterTest extends AbstractTestCase
{
    public function testCallsToDecorated(): void
    {
        $data = new \stdClass();

        $stub = new ContextAwareDataPersisterStub();
        $doctrineOrmPersister = new DoctrineOrmDataPersister($stub);

        $doctrineOrmPersister->remove($data);

        $supports = $doctrineOrmPersister->supports($data);
        $return = $doctrineOrmPersister->persist($data);

        self::assertTrue($supports);
        self::assertEquals(\spl_object_hash($data), \spl_object_hash($return));
        self::assertEquals($stub->getCalls()['persist'], [$data, []]);
        self::assertEquals($stub->getCalls()['remove'], [$data, []]);
        self::assertEquals($stub->getCalls()['supports'], [$data, []]);
    }
}
