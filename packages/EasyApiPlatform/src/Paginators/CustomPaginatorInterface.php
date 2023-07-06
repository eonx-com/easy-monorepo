<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Paginators;

use Symfony\Component\Serializer\Annotation\Groups;

interface CustomPaginatorInterface
{
    public const SERIALIZER_GROUP = 'resource:pagination';

    /**
     * @return mixed[]
     */
    #[Groups([self::SERIALIZER_GROUP])]
    public function getItems(): array;

    /**
     * @return mixed[]
     */
    #[Groups([self::SERIALIZER_GROUP])]
    public function getPagination(): array;
}
