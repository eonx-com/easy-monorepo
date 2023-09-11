<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Bridge\Symfony\Stubs;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

final class SerializerContextBuilderStub implements SerializerContextBuilderInterface
{
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        return [];
    }
}
