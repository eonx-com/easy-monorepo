<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\Enum;

enum ConfigTag: string
{
    case HealthChecker = 'easy_serverless.health_checker';

    case StateChecker = 'easy_serverless.state_checker';
}
