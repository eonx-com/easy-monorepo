<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_AWS_PKCS11_AAD = 'easy_encryption.aws_pkcs11_aad';

    public const PARAM_AWS_PKCS11_AWS_REGION = 'easy_encryption.aws_pkcs11_aws_region';

    public const PARAM_AWS_PKCS11_AWS_ROLE_ARN = 'easy_encryption.aws_pkcs11_aws_role_arn';

    public const PARAM_AWS_PKCS11_CLOUD_HSM_CLUSTER_ID = 'easy_encryption.aws_pkcs11_cloud_hsm_cluster_id';

    public const PARAM_AWS_PKCS11_CLOUD_HSM_CONFIGURE_TOOL_OPTIONS = 'easy_encryption.aws_pkcs11_cloud_hsm_configure_tool_options';

    public const PARAM_AWS_PKCS11_DISABLE_KEY_AVAILABILITY_CHECK = 'easy_encryption.aws_pkcs11_disable_key_availability_check';

    public const PARAM_AWS_PKCS11_HSM_CA_CERT = 'easy_encryption.aws_pkcs11_hsm_ca_cert';

    public const PARAM_AWS_PKCS11_HSM_IP_ADDRESS = 'easy_encryption.aws_pkcs11_hsm_ip_address';

    public const PARAM_AWS_PKCS11_SERVER_CLIENT_CERT_FILE = 'easy_encryption.aws_pkcs11_server_client_cert_file';

    public const PARAM_AWS_PKCS11_SERVER_CLIENT_KEY_FILE = 'easy_encryption.aws_pkcs11_server_client_key_file';

    public const PARAM_AWS_PKCS11_USER_PIN = 'easy_encryption.aws_pkcs11_user_pin';

    public const PARAM_AWS_PKCS11_USE_CLOUD_HSM_CONFIGURE_TOOL = 'easy_encryption.aws_pkcs11_use_cloud_hsm_configure_tool';

    public const PARAM_DEFAULT_ENCRYPTION_KEY = 'easy_encryption.default_encryption_key';

    public const PARAM_DEFAULT_KEY_NAME = 'easy_encryption.default_key_name';

    public const PARAM_DEFAULT_SALT = 'easy_encryption.default_salt';

    public const SERVICE_DEFAULT_KEY_RESOLVER = 'easy_encryption.default_key_resolver';

    public const TAG_ENCRYPTION_KEY_RESOLVER = 'easy_encryption.encryption_key_resolver';
}
