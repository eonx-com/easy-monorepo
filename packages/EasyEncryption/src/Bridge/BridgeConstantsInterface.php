<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_DEFAULT_KEY_NAME = 'easy_encryption.default_key_name';

    /**
     * @var string
     */
    public const PARAM_DEFAULT_ENCRYPTION_KEY = 'easy_encryption.default_encryption_key';

    /**
     * @var string
     */
    public const PARAM_DEFAULT_SALT = 'easy_encryption.default_salt';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_USER_PIN = 'easy_encryption.aws_pkcs11_user_pin';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_HSM_CA_CERT = 'easy_encryption.aws_pkcs11_hsm_ca_cert';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_DISABLE_KEY_AVAILABILITY_CHECK = 'easy_encryption.aws_pkcs11_disable_key_availability_check';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_HSM_IP_ADDRESS = 'easy_encryption.aws_pkcs11_hsm_ip_address';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_CLOUD_HSM_CLUSTER_ID = 'easy_encryption.aws_pkcs11_cloud_hsm_cluster_id';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_AWS_REGION = 'easy_encryption.aws_pkcs11_aws_region';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_AAD = 'easy_encryption.aws_pkcs11_aad';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_SERVER_CLIENT_CERT_FILE = 'easy_encryption.aws_pkcs11_server_client_cert_file';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_SERVER_CLIENT_KEY_FILE = 'easy_encryption.aws_pkcs11_server_client_key_file';

    /**
     * @var string
     */
    public const PARAM_AWS_PKCS11_HSM_SDK_OPTIONS = 'easy_encryption.aws_pkcs11_hsm_sdk_options';

    /**
     * @var string
     */
    public const SERVICE_DEFAULT_KEY_RESOLVER = 'easy_encryption.default_key_resolver';

    /**
     * @var string
     */
    public const TAG_ENCRYPTION_KEY_RESOLVER = 'easy_encryption.encryption_key_resolver';
}
