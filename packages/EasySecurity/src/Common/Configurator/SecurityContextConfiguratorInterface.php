<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityInterface;
use Symfony\Component\HttpFoundation\Request;

interface SecurityContextConfiguratorInterface extends HasPriorityInterface
{
    public function configure(SecurityContextInterface $context, Request $request): void;
}
