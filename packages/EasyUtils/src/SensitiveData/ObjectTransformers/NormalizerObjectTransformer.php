<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\ObjectTransformers;

use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;
use Throwable;

final class NormalizerObjectTransformer extends AbstractObjectTransformer implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    #[SubscribedService(key: NormalizerInterface::class)]
    public function getNormalizer(): NormalizerInterface
    {
        return $this->container->get(NormalizerInterface::class);
    }

    public function supports(object $object): bool
    {
        return true;
    }

    public function transform(object $object): array
    {
        try {
            $normalized = $this->getNormalizer()
                ->normalize($object);
        } catch (Throwable) {
            return [];
        }

        return match (true) {
            \is_array($normalized) => $normalized,
            $object instanceof DateTimeInterface => ['datetime' => $normalized],
            $object instanceof AbstractUid => ['uuid' => $normalized],
            default => [\gettype($normalized) => $normalized]
        };
    }
}
