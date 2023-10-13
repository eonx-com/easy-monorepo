<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\Controller;

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
