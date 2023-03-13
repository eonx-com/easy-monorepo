<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge;

interface BridgeConstantsInterface
{
    public const DEFAULT_UUID_VERSION = 6;

    public const EXTENSION_NAME = 'easy_random';

    public const PARAM_DEFAULT_UUID_VERSION = self::EXTENSION_NAME . '.param.default_uuid_version';

    public const SERVICE_RAMSEY_UUID4 = self::EXTENSION_NAME . '.ramsey_uuid4';

    public const SERVICE_RAMSEY_UUID6 = self::EXTENSION_NAME . '.ramsey_uuid6';

    public const SERVICE_SYMFONY_UUID4 = self::EXTENSION_NAME . '.symfony_uuid4';

    public const SERVICE_SYMFONY_UUID6 = self::EXTENSION_NAME . '.symfony_uuid6';
}
