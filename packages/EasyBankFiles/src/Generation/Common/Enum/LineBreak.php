<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Common\Enum;

enum LineBreak: string
{
    case Mac = "\r";

    case Unix = "\n";

    case Windows = "\r\n";
}
