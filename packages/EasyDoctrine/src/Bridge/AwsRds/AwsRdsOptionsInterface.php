<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds;

interface AwsRdsOptionsInterface
{
    public const AWS_REGION = 'easy_doctrine.aws_rds.region';

    public const AWS_USERNAME = 'easy_doctrine.aws_rds.username';

    public const IAM_ENABLED = 'easy_doctrine.aws_rds.iam.enabled';

    public const SSL_ENABLED = 'easy_doctrine.aws_rds.ssl.enabled';
}
