<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds;

interface AwsRdsOptionsInterface
{
    public const ALL_OPTIONS = [
        self::AWS_REGION,
        self::AWS_USERNAME,
        self::IAM_ENABLED,
        self::SSL_ENABLED,
        self::SSL_MODE,
    ];

    public const AWS_REGION = 'easy_doctrine.aws_rds.region';

    public const AWS_USERNAME = 'easy_doctrine.aws_rds.username';

    public const IAM_ENABLED = 'easy_doctrine.aws_rds.iam.enabled';

    public const SSL_ENABLED = 'easy_doctrine.aws_rds.ssl.enabled';

    public const SSL_MODE = 'easy_doctrine.aws_rds.ssl.mode';
}
