<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\AppStateResetters;

use EonX\EasySwoole\AppStateResetters\AbstractAppStateResetter;

use function Symfony\Component\String\u;

abstract class AbstractSymfonyServicesAppStateResetter extends AbstractAppStateResetter
{
    /**
     * @param \Traversable<mixed> $resettableServices
     * @param string[] $resetMethods
     * @param int|null $priority
     */
    public function __construct(
        private readonly \Traversable $resettableServices,
        private readonly array $resetMethods,
        ?int $priority = null
    ) {
        parent::__construct($priority);
    }

    public function resetState(): void
    {
        foreach ($this->resettableServices as $id => $service) {
            foreach ((array)$this->resetMethods[$id] as $resetMethod) {
                $resetMethod = u($resetMethod);

                if ($resetMethod->startsWith('?')) {
                    $resetMethod = $resetMethod->trimStart('?');

                    if (\method_exists($service, (string)$resetMethod) === false) {
                        continue;
                    }
                }

                if ($this->shouldReset($id)) {
                    $service->{(string)$resetMethod}();
                }
            }
        }
    }

    abstract protected function shouldReset(string $service): bool;
}
