<?php
declare(strict_types=1);

namespace EonX\EasyServerless\State\Resetter;

use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Traversable;

use function Symfony\Component\String\u;

final class SymfonyServicesAppStateResetter extends ServicesResetter
{
    /**
     * @param string[] $resetMethods
     */
    public function __construct(
        private readonly Traversable $resettableServices,
        private readonly array $resetMethods,
    ) {
        parent::__construct($resettableServices, $resetMethods);
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
