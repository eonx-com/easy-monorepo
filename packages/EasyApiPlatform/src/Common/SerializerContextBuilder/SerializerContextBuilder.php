<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\SerializerContextBuilder;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\SerializerContextBuilderInterface as LegacySerializerContextBuilderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use EonX\EasyApiPlatform\Common\Paginator\CustomPaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

// @todo Remove this class when dropping support for API Platform 3
if (\interface_exists(LegacySerializerContextBuilderInterface::class)) {
    final readonly class SerializerContextBuilder implements
        SerializerContextBuilderInterface,
        LegacySerializerContextBuilderInterface
    {
        public function __construct(
            private SerializerContextBuilderInterface $decorated,
        ) {
        }

        public function createFromRequest(
            Request $request,
            bool $normalization,
            ?array $extractedAttributes = null,
        ): array {
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
} else {
    final readonly class SerializerContextBuilder implements SerializerContextBuilderInterface
    {
        public function __construct(
            private SerializerContextBuilderInterface $decorated,
        ) {
        }

        public function createFromRequest(
            Request $request,
            bool $normalization,
            ?array $extractedAttributes = null,
        ): array {
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
}
