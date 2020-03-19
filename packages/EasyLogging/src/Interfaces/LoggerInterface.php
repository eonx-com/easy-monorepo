<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

use EoneoPay\Externals\Logger\Interfaces\LoggerInterface as ExternalsLoggerInterface;

interface LoggerInterface extends ExternalsLoggerInterface
{
    /**
     * @var string[]
     */
    public const LEVELS = [
        self::LEVEL_ALERT,
        self::LEVEL_CRITICAL,
        self::LEVEL_DEBUG,
        self::LEVEL_EMERGENCY,
        self::LEVEL_ERROR,
        self::LEVEL_INFO,
        self::LEVEL_INFO,
        self::LEVEL_NOTICE,
        self::LEVEL_WARNING,
    ];

    /**
     * @var string
     */
    public const LEVEL_ALERT = 'alert';

    /**
     * @var string
     */
    public const LEVEL_CRITICAL = 'critical';

    /**
     * @var string
     */
    public const LEVEL_DEBUG = 'debug';

    /**
     * @var string
     */
    public const LEVEL_EMERGENCY = 'emergency';

    /**
     * @var string
     */
    public const LEVEL_ERROR = 'error';

    /**
     * @var string
     */
    public const LEVEL_INFO = 'info';

    /**
     * @var string
     */
    public const LEVEL_NOTICE = 'notice';

    /**
     * @var string
     */
    public const LEVEL_WARNING = 'warning';
}
