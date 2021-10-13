<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_DISALLOWED_PROPERTIES = 'easy_activity.disallowed_properties';

    /**
     * @var string
     */
    public const PARAM_EASY_DOCTRINE_SUBSCRIBER_ENABLED = 'easy_activity.easy_doctrine_subscriber_enabled';

    /**
     * @var string
     */
    public const PARAM_SUBJECTS = 'easy_activity.subjects';

    /**
     * @var string
     */
    public const PARAM_TABLE_NAME = 'easy_activity.table_name';
}
