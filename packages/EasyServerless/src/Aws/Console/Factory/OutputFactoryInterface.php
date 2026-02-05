<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Console\Factory;

use Symfony\Component\Console\Output\OutputInterface;

interface OutputFactoryInterface
{
    public function create(?OutputInterface $output = null): OutputInterface;
}
