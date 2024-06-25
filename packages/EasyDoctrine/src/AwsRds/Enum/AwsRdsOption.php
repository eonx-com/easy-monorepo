<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Enum;

enum AwsRdsOption: string
{
    case IamEnabled = 'easy_doctrine.aws_rds.iam.enabled';

    case Region = 'easy_doctrine.aws_rds.region';

    case SslEnabled = 'easy_doctrine.aws_rds.ssl.enabled';

    case SslMode = 'easy_doctrine.aws_rds.ssl.mode';

    case Username = 'easy_doctrine.aws_rds.username';
}
