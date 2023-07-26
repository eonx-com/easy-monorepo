<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_AWS_RDS_IAM_AUTH_TOKEN_LIFETIME_IN_MINUTES
        = 'easy_doctrine.aws_rds.iam.auth_token_lifetime_in_minutes';

    public const PARAM_AWS_RDS_IAM_AWS_REGION = 'easy_doctrine.aws_rds.iam.aws_region';

    public const PARAM_AWS_RDS_IAM_AWS_USERNAME = 'easy_doctrine.aws_rds.iam.aws_username';

    public const PARAM_AWS_RDS_SSL_CA_PATH = 'easy_doctrine.aws_rds.ssl.ca_path';

    public const PARAM_AWS_RDS_SSL_MODE = 'easy_doctrine.aws_rds.ssl.mode';

    public const PARAM_DEFERRED_DISPATCHER_ENTITIES = 'easy_doctrine.deferred_dispatcher_entities';

    public const SERVICE_AWS_RDS_IAM_CACHE = 'easy_doctrine.aws_rds_iam_cache';
}
