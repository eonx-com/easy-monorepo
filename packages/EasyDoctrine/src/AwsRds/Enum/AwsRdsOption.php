<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Enum;

enum AwsRdsOption: string
{
    case AssumeRoleArn = 'easy_doctrine.aws_rds.iam.assume_role_arn';

    case AssumeRoleDurationSeconds = 'easy_doctrine.aws_rds.iam.assume_role_duration_seconds';

    case AssumeRoleRegion = 'easy_doctrine.aws_rds.iam.assume_role_region';

    case AssumeRoleSessionName = 'easy_doctrine.aws_rds.iam.assume_role_session_name';

    case IamEnabled = 'easy_doctrine.aws_rds.iam.enabled';

    case Region = 'easy_doctrine.aws_rds.region';

    case SslEnabled = 'easy_doctrine.aws_rds.ssl.enabled';

    case SslMode = 'easy_doctrine.aws_rds.ssl.mode';

    case Username = 'easy_doctrine.aws_rds.username';
}
