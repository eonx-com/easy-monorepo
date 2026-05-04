<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\Enum;

enum ConfigParam: string
{
    case AwsRdsIamAssumeRoleArn = 'easy_doctrine.aws_rds.iam.assume_role_arn';

    case AwsRdsIamAssumeRoleDurationSeconds = 'easy_doctrine.aws_rds.iam.assume_role_duration_seconds';

    case AwsRdsIamAssumeRoleRegion = 'easy_doctrine.aws_rds.iam.assume_role_region';

    case AwsRdsIamAssumeRoleSessionName = 'easy_doctrine.aws_rds.iam.assume_role_session_name';

    case AwsRdsIamAuthTokenLifetimeInMinutes = 'easy_doctrine.aws_rds.iam.auth_token_lifetime_in_minutes';

    case AwsRdsIamAwsRegion = 'easy_doctrine.aws_rds.iam.aws_region';

    case AwsRdsIamAwsUsername = 'easy_doctrine.aws_rds.iam.aws_username';

    case AwsRdsSslCaPath = 'easy_doctrine.aws_rds.ssl.ca_path';

    case AwsRdsSslMode = 'easy_doctrine.aws_rds.ssl.mode';

    case DeferredDispatcherEntities = 'easy_doctrine.deferred_dispatcher_entities';

    case EntityManagerLazy = 'easy_doctrine.entity_manager.lazy';
}
