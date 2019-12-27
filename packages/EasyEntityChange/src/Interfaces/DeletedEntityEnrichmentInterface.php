<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Interfaces;

interface DeletedEntityEnrichmentInterface
{
    /**
     * Builds an array of metadata around the object deletion, which will be passed into the
     * EntityChangeEvent.
     *
     * @param object $entity
     *
     * @return mixed[]
     */
    public function getMetadata(object $entity): array;
}
