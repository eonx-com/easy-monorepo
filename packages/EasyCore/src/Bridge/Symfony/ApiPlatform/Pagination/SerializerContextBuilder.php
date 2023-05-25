<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

final class SerializerContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * @var \ApiPlatform\Serializer\SerializerContextBuilderInterface
     */
    private $decorated;

    public function __construct(SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param null|mixed[] $extractedAttributes
     *
     * @return mixed[]
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $operationType = $context['operation_type'] ?? null;

        // Customize context only for collection get
        if ($operationType === CustomPaginatorInterface::OPERATION_TYPE && $request->isMethod(Request::METHOD_GET)) {
            $context['groups'] = \array_merge($context['groups'] ?? [], [CustomPaginatorInterface::SERIALIZER_GROUP]);
        }

        return $context;
    }
}
