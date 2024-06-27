<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bundle\Enum;

enum ConfigServiceId: string
{
    case AuthorizationMatrixCache = 'easy_security.authorization_matrix_cache';

    case Logger = 'easy_security.logger';
}
