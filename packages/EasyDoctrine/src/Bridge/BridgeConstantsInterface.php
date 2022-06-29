<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_AWS_RDS_IAM_REGION = 'easy_doctrine.aws_rds_iam_region';

    /**
     * @var string
     */
    public const PARAM_AWS_RDS_IAM_USERNAME = 'easy_doctrine.aws_rds_iam_username';

    /**
     * @var string
     */
    public const PARAM_AWS_RDS_IAM_CACHE_EXPIRY_IN_SECONDS = 'easy_doctrine.aws_rds_iam_cache_expiry_in_seconds';

    /**
     * @var string
     */
    public const PARAM_DEFERRED_DISPATCHER_ENTITIES = 'easy_doctrine.deferred_dispatcher_entities';

    /**
     * @var string
     */
    public const SERVICE_AWS_RDS_IAM_CACHE = 'easy_doctrine.aws_rds_iam_cache';
}
