<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/Controller/DummyController.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\Controller;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\Controller;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/Controller/DummyController.php

use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

#[AsController]
final class DummyController
{
    #[Route('/dummy-action')]
    public function number(): never
    {
        throw new NotNormalizableValueException('Exception supported by API Platform Builders, but thrown ' .
            'outside API Platform denormalization logic.');
    }
}
