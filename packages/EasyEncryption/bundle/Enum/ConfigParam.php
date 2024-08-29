<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bundle\Enum;

enum ConfigParam: string
{
    case AwsCloudHsmAad = 'easy_encryption.aws_cloud_hsm.aad';

    case AwsCloudHsmCaCertFile = 'easy_encryption.aws_cloud_hsm.ca_cert_file';

    case AwsCloudHsmClusterId = 'easy_encryption.aws_cloud_hsm.cluster_id';

    case AwsCloudHsmDisableKeyAvailabilityCheck = 'easy_encryption.aws_cloud_hsm.disable_key_availability_check';

    case AwsCloudHsmEnabled = 'easy_encryption.aws_cloud_hsm.enabled';

    case AwsCloudHsmIpAddress = 'easy_encryption.aws_cloud_hsm.ip_address';

    case AwsCloudHsmRegion = 'easy_encryption.aws_cloud_hsm.region';

    case AwsCloudHsmRoleArn = 'easy_encryption.aws_cloud_hsm.role_arn';

    case AwsCloudHsmSdkOptions = 'easy_encryption.aws_cloud_hsm.sdk_options';

    case AwsCloudHsmServerClientCertFile = 'easy_encryption.aws_cloud_hsm.server_client_cert_file';

    case AwsCloudHsmServerClientKeyFile = 'easy_encryption.aws_cloud_hsm.server_client_key_file';

    case AwsCloudHsmSignKeyName = 'easy_encryption.aws_cloud_hsm.sign_key_name';

    case AwsCloudHsmUseConfigureTool = 'easy_encryption.aws_cloud_hsm.use_configure_tool';

    case AwsCloudHsmUserPin = 'easy_encryption.aws_cloud_hsm.user_pin';

    case DefaultEncryptionKey = 'easy_encryption.default_encryption_key';

    case DefaultKeyName = 'easy_encryption.default_key_name';

    case DefaultSalt = 'easy_encryption.default_salt';

    case FullyEncryptedMessages = 'easy_encryption.fully_encrypted_messages';

    case MaxChunkSize = 'easy_encryption.max_chunk_size';
}
