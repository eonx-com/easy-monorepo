<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\CircularReferenceHandler;

interface CircularReferenceHandlerInterface
{
    public function __invoke(object $object, string $format, array $context): string;
}
