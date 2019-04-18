<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Decoders;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use StepTheFkUp\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use StepTheFkUp\EasyApiToken\Traits\EasyApiTokenDecoderTrait;

final class ApiKeyAsBasicAuthUsernameDecoder implements EasyApiTokenDecoderInterface
{
    use EasyApiTokenDecoderTrait;

    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            return null; // If Authorization doesn't start with Basic, return null
        }

        $authorization = \explode(':', (string)\base64_decode($authorization));

        if (empty(\trim($authorization[0] ?? '')) === true || empty(\trim($authorization[1] ?? '')) === false) {
            return null; // If Authorization doesn't contain ONLY a username, return null
        }

        return new ApiKeyEasyApiToken(\trim($authorization[0]));
    }
}

\class_alias(
    ApiKeyAsBasicAuthUsernameDecoder::class,
    'LoyaltyCorp\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder',
    false
);
