<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bundle\Enum;

enum ConfigParam: string
{
    case DisallowedProperties = 'easy_activity.disallowed_properties';

    case EasyDoctrineSubscriberEnabled = 'easy_activity.easy_doctrine_subscriber_enabled';

    case Subjects = 'easy_activity.subjects';

    case TableName = 'easy_activity.table_name';
}
