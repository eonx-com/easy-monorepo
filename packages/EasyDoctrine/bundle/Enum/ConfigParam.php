<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\Enum;

enum ConfigParam: string
{
    case AwsRdsIamAuthTokenLifetimeInMinutes = 'easy_doctrine.aws_rds.iam.auth_token_lifetime_in_minutes';

    case AwsRdsIamAwsRegion = 'easy_doctrine.aws_rds.iam.aws_region';

    case AwsRdsIamAwsUsername = 'easy_doctrine.aws_rds.iam.aws_username';

    case AwsRdsIamLogger  = 'easy_doctrine.aws_rds.iam.logger';

    case AwsRdsSslLogger = 'easy_doctrine.aws_rds.ssl.logger';

    case AwsRdsSslCaPath = 'easy_doctrine.aws_rds.ssl.ca_path';

    case AwsRdsSslMode = 'easy_doctrine.aws_rds.ssl.mode';

    case DeferredDispatcherEntities = 'easy_doctrine.deferred_dispatcher_entities';
}
