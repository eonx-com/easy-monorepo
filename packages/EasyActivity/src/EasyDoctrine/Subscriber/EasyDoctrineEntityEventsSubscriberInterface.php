<?php
declare(strict_types=1);

namespace EonX\EasyActivity\EasyDoctrine\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface EasyDoctrineEntityEventsSubscriberInterface extends EventSubscriberInterface
{
    public function disable(): void;

    public function enable(): void;
}
