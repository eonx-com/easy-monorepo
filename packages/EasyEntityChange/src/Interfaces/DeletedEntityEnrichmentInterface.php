<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Interfaces;

interface DeletedEntityEnrichmentInterface
{
    /**
     * @return mixed[]
     */
    public function getMetadata(object $entity): array;
}
