<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Serializers;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use EonX\EasyApiPlatform\Paginators\CustomPaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class SerializerContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(private SerializerContextBuilderInterface $decorated)
    {
    }

    /**
     * @param null|mixed[] $extractedAttributes
     *
     * @return mixed[]
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $operation = $context['operation'] ?? null;

        // Customize context only for collection get
        if ($operation instanceof GetCollection) {
            $groups = (array)($context['groups'] ?? []);

            if (\in_array(CustomPaginatorInterface::SERIALIZER_GROUP, $groups, true) === false) {
                $groups[] = CustomPaginatorInterface::SERIALIZER_GROUP;
            }

            $context['groups'] = $groups;
        }

        return $context;
    }
}
