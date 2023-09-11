<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\AppStateResetters;

use EonX\EasySwoole\Interfaces\AppStateResetterInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Traversable;

use function Symfony\Component\String\u;

final class SymfonyServicesAppStateResetter extends ServicesResetter implements AppStateResetterInterface
{
    use HasPriorityTrait;

    /**
     * @param string[] $resetMethods
     */
    public function __construct(
        private Traversable $resettableServices,
        private array $resetMethods,
        ?int $priority = null,
    ) {
        parent::__construct($resettableServices, $resetMethods);

        $this->doSetPriority($priority);
    }

    public function reset(): void
    {
        $this->resetState();
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

    protected function shouldReset(string $service): bool
    {
        return \str_starts_with($service, 'cache.') === false
            && \str_starts_with($service, 'cache_') === false
            && \str_ends_with($service, '.cache') === false
            && \str_ends_with($service, '_cache') === false;
    }
}
