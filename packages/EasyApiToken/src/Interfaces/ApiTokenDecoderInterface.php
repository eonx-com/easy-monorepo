<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface ApiTokenDecoderInterface
{
    public const NAME_BASIC = 'basic';

    public const NAME_CHAIN = 'chain';

    public const NAME_JWT_HEADER = 'jwt-header';

    public const NAME_JWT_PARAM = 'jwt-param';

    public const NAME_USER_APIKEY = 'user-apikey';

    public function decode(Request $request): ?ApiTokenInterface;

    public function getName(): string;
}
