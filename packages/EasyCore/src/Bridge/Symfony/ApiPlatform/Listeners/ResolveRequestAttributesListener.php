<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Listeners;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Event\DataPersisterResolvedEvent;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Exception\RequestAttributesSetterNotFoundException;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Exception\RequestAttributesSetterNotFoundException as NotFoundException;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\RequestAttributesAwareInterface;
use EonX\EasyCore\Bridge\Symfony\Event\KernelEventListenerTrait;
use EonX\EasyCore\Bridge\Symfony\Interfaces\EventListenerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

final class ResolveRequestAttributesListener implements EventListenerInterface
{
    use KernelEventListenerTrait;

    /**
     * @var \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface
     */
    private $argumentResolver;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(RequestStack $requestStack, ?ArgumentResolverInterface $argumentResolver = null)
    {
        $this->argumentResolver = $argumentResolver ?? new ArgumentResolver();
        $this->request = $requestStack->getMasterRequest();
    }

    public function __invoke(DataPersisterResolvedEvent $event): void
    {
        if ($this->request === null) {
            return;
        }

        $dataPersister = $event->getDataPersister();

        if ($dataPersister instanceof RequestAttributesAwareInterface === false) {
            return;
        }

        /** @var \EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\RequestAttributesAwareInterface $dataPersister */

        $setter = $dataPersister->getRequestAttributesSetter();

        if (\method_exists($dataPersister, $setter) === false) {
            throw $this->newMethodNotFound($dataPersister);
        }

        $dataPersister->{$setter}(...$this->argumentResolver->getArguments($this->request, [$dataPersister, $setter]));
    }

    /**
     * @return mixed[]
     *
     * @throws \ReflectionException
     */
    private function findAlternatives(RequestAttributesAwareInterface $dataPersister): array
    {
        $alternatives = [];
        $methods = (new \ReflectionClass($dataPersister))->getMethods();
        $setter = $dataPersister->getRequestAttributesSetter();
        $threshold = 1e3;

        $excluded = [
            '__construct',
            'supports',
            'persist',
            'remove',
            'getApiResourceClass',
            'getRequestAttributesSetter',
        ];

        $filter = static function (int $lev) use ($threshold): bool {
            return $lev < 2 * $threshold;
        };

        foreach ($methods as $method) {
            $name = $method->getName();

            if (\in_array($name, $excluded, true)) {
                continue;
            }

            $lev = \levenshtein($setter, $name);

            if ($lev <= \strlen($name) / 3) {
                $alternatives[$name] = $lev;
            }
        }

        $alternatives = \array_filter($alternatives, $filter);
        \ksort($alternatives, \SORT_NATURAL | \SORT_FLAG_CASE);

        return \array_keys($alternatives);
    }

    private function newMethodNotFound(RequestAttributesAwareInterface $dataPersister): NotFoundException
    {
        $message = \sprintf(
            'Request attributes setter "%s" on "%s" not found.',
            $dataPersister->getRequestAttributesSetter(),
            \get_class($dataPersister)
        );

        $alternatives = $this->findAlternatives($dataPersister);

        if (empty($alternatives) === false) {
            $message .= \sprintf(' Did you mean one of the following: "%s"', \implode('", "', $alternatives));
        }

        return new RequestAttributesSetterNotFoundException($message);
    }
}
