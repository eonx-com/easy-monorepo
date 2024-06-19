<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Enum;

enum AwsRdsOption: string
{
    case AwsRegion = 'easy_doctrine.aws_rds.region';

    case AwsUsername = 'easy_doctrine.aws_rds.username';

    case IamEnabled = 'easy_doctrine.aws_rds.iam.enabled';

    case SslEnabled = 'easy_doctrine.aws_rds.ssl.enabled';

    case SslMode = 'easy_doctrine.aws_rds.ssl.mode';
}
