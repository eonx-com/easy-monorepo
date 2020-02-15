<?php
declare(strict_types=1);

namespace EonX\EasyTest\Console;

use Symfony\Component\Console\Application;

final class EasyTestApplication extends Application
{
    /** @var string */
    public const VERSION = '1.0.0';

    /**
     * EasyTestApplication constructor.
     */
    public function __construct()
    {
        parent::__construct('easy-test', self::VERSION);
    }
}
