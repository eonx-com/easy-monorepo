<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\Enum;

enum ConfigServiceId: string
{
    case AwsRdsIamCache = 'easy_doctrine.aws_rds_iam_cache';

    case AwsRdsIamLogger = 'easy_doctrine.aws_rds.iam.logger';

    case AwsRdsSslLogger = 'easy_doctrine.aws_rds.ssl.logger';

    case DeletedEntityCopier = 'easy_doctrine.deleted_entity_copier';
}
