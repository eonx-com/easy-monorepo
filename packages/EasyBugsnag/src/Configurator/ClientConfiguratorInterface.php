<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Configurator;

use Bugsnag\Client;
use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface ClientConfiguratorInterface extends HasPriorityInterface
{
    public function configure(Client $bugsnag): void;
}
