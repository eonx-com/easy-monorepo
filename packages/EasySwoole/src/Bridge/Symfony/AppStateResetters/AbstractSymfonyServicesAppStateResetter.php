<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\AppStateResetters;

use EonX\EasySwoole\AppStateResetters\AbstractAppStateResetter;

abstract class AbstractSymfonyServicesAppStateResetter extends AbstractAppStateResetter
{
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
                if ($resetMethod[0] === '?'
                    && \method_exists($service, $resetMethod = \substr($resetMethod, 1)) === false) {
                    continue;
                }

                if ($this->shouldReset($id)) {
                    $service->$resetMethod();
                }
            }
        }
    }

    abstract protected function shouldReset(string $service): bool;
}
