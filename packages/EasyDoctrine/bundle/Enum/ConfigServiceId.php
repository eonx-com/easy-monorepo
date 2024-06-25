<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\Enum;

enum ConfigServiceId: string
{
    case AwsRdsIamCache = 'easy_doctrine.aws_rds_iam_cache';
}
