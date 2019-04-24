<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Decoders;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use LoyaltyCorp\EasyApiToken\Tokens\BasicAuthEasyApiToken;
use LoyaltyCorp\EasyApiToken\Traits\EasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;

final class BasicAuthDecoder implements EasyApiTokenDecoderInterface
{
    use EasyApiTokenDecoderTrait;

    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            return null; // If Authorization doesn't start with Basic, return null
        }

        $authorization = \explode(':', (string)\base64_decode($authorization));

        if (empty(\trim($authorization[0] ?? '')) || empty(\trim($authorization[1] ?? ''))) {
            return null; // If Authorization doesn't contain a username AND a password, return null
        }

        return new BasicAuthEasyApiToken(\trim($authorization[0]), \trim($authorization[1]));
    }
}

\class_alias(
    BasicAuthDecoder::class,
    'StepTheFkUp\EasyApiToken\Decoders\BasicAuthDecoder',
    false
);
