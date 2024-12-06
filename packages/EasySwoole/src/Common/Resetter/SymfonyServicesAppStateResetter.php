<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Resetter;

use EonX\EasyUtils\Common\Helper\HasPriorityTrait;
use Symfony\Contracts\Service\ResetInterface;
use Traversable;

use function Symfony\Component\String\u;

final class SymfonyServicesAppStateResetter implements AppStateResetterInterface, ResetInterface
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
