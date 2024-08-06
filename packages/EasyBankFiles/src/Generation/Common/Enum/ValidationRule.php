<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Common\Enum;

enum ValidationRule: string
{
    case Alpha = '/[^A-Za-z0-9 &\',-\\.\\/\\+\\$\\!%\\(\\)\\*\\#=:\\?\\[\\]_\\^@]/';

    case Bsb = '/^\\d{3}(\\-)\\d{3}/';

    case Date = 'date';

    case Numeric = '/[^0-9-]/';

    case Required = 'required';
}
