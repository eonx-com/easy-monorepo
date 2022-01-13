<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use EonX\EasyActivity\Bridge\Symfony\Serializers\CircularReferenceHandlerInterface;

final class CircularReferenceHandlerStub implements CircularReferenceHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(object $object, string $format, array $context)
    {
        return 'circular-reference-uuid';
    }
}
