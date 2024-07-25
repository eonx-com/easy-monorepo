<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Enum;

enum Alphabet: string
{
    case Ambiguous = '-[]\\;\',./!()_{}:"<>?';

    case Lowercase = 'abcdefghijklmnopqrstuvwxyz';

    case Numeric = '0123456789';

    case Similar = 'iIlLoOqQsS015!$';

    case Symbol = '-=[]\\;\',./~!@#$%^&*()_+{}|:"<>?';

    case Uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    case Vowel = 'aAeEiIoOuU';
}
