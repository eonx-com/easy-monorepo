<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Decoders;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenDecoderInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Tokens\ApiKeyApiToken;
use StepTheFkUp\ApiToken\Traits\ApiTokenDecoderTrait;

final class ApiKeyAsBasicAuthUsernameDecoder implements ApiTokenDecoderInterface
{
    use ApiTokenDecoderTrait;

    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface
     */
    public function decode(ServerRequestInterface $request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            return null; // If Authorization doesn't start with Basic, return null
        }

        $authorization = \explode(':', (string)\base64_decode($authorization));

        if (empty(\trim($authorization[0] ?? '')) === true || empty(\trim($authorization[1] ?? '')) === false) {
            return null; // If Authorization doesn't contain ONLY a username, return null
        }

        return new ApiKeyApiToken(\trim($authorization[0]));
    }
}
