<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Uid\Hook;

use EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface;
use ReflectionProperty;
use Symfony\Component\Uid\UuidV6;

/**
 * UuidV6 picks a random node once per process and keeps it in a static property, so a node picked before the snapshot
 * would be shared by every restored execution environment, leaving only the timestamp and the 14-bit clock sequence to
 * keep their UUIDs apart.
 */
final readonly class RegenerateUuidV6NodeHook implements AfterRestoreHookInterface
{
    public function afterRestore(): void
    {
        $node = new ReflectionProperty(UuidV6::class, 'node');

        // UuidV6 picks the node on its first generation
        if ($node->isInitialized() === false) {
            return;
        }

        // Same format as the node picked by UuidV6::generate(), the multicast bit marks a random node
        $node->setValue(null, \sprintf('%06x%06x', \random_int(0, 0xFFFFFF) | 0x010000, \random_int(0, 0xFFFFFF)));
    }
}
