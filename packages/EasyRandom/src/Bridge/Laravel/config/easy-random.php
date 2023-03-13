<?php

declare(strict_types=1);

use EonX\EasyRandom\Bridge\BridgeConstantsInterface;

return [
    // The UUID version by default. Possible values: 4, 6
    'default_uuid_version' => BridgeConstantsInterface::DEFAULT_UUID_VERSION,

    // Service ID of the UUID V4 generator to use
    'uuid_v4_generator' => null,

    // Service ID of the UUID V6 generator to use
    'uuid_v6_generator' => null,
];
