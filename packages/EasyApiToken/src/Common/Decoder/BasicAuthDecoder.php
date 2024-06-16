<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Decoder;

use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use EonX\EasyApiToken\Common\ValueObject\BasicAuth;
use Symfony\Component\HttpFoundation\Request;

final class BasicAuthDecoder extends AbstractDecoder
{
    public function decode(Request $request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            // If Authorization doesn't start with Basic, return null
            return null;
        }

        $original = $authorization;
        $authorization = \explode(':', (string)\base64_decode($authorization, true));
        $username = \trim($authorization[0] ?? '');
        $password = \trim($authorization[1] ?? '');

        if ($username === '' || $password === '') {
            // If Authorization doesn't contain a username AND a password, return null
            return null;
        }

        return new BasicAuth($username, $password, $original);
    }
}
