<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Hash;

use EonX\EasySsm\Services\Hash\HashRepository;
use EonX\EasySsm\Tests\AbstractTestCase;
use Symfony\Component\Filesystem\Filesystem;

final class HashRepositoryTest extends AbstractTestCase
{
    public function testFileDoesntExist(): void
    {
        $repository = new HashRepository(new Filesystem());

        self::assertNull($repository->get('not-found'));
    }

    public function testSaveThenGet(): void
    {
        $name = 'test';
        $hash = 'my-hash';

        $repository = new HashRepository(new Filesystem());
        $repository->save($name, $hash);

        self::assertEquals($hash, $repository->get($name));
    }
}
