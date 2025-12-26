<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\Paginator;

use Symfony\Component\Serializer\Attribute\Groups;

interface CustomPaginatorInterface
{
    public const SERIALIZER_GROUP = 'resource:pagination';

    #[Groups([self::SERIALIZER_GROUP])]
    public function getItems(): array;

    #[Groups([self::SERIALIZER_GROUP])]
    public function getPagination(): array;
}
