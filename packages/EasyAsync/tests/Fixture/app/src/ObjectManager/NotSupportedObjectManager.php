<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Fixture\App\ObjectManager;

use Doctrine\Persistence\ObjectManagerDecorator;

/**
 * @extends \Doctrine\Persistence\ObjectManagerDecorator<\Doctrine\Persistence\ObjectManager>
 */
final class NotSupportedObjectManager extends ObjectManagerDecorator
{
    public function clear(): void
    {
    }
}
