<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Implementations\Auth0;

use GuzzleHttp\Exception\RequestException;
use LoyaltyCorp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException;
use LoyaltyCorp\EasyIdentity\Exceptions\LoginFailedException;
use LoyaltyCorp\EasyIdentity\Implementations\AbstractIdentityService;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceInterface;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceNamesInterface;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface;

final class Auth0IdentityService extends AbstractIdentityService implements IdentityServiceInterface
{
    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory
     */
    private $authFactory;

    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory
     */
    private $managementFactory;

    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory
     */
    private $tokenVerifierFactory;

    /**
     * Auth0IdentityService constructor.
     *
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config $config
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $managementFactory
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory $tokenVerifierFactory
     */
    public function __construct(
        AuthenticationApiClientFactory $authFactory,
        Config $config,
        ManagementApiClientFactory $managementFactory,
        TokenVerifierFactory $tokenVerifierFactory
    ) {
        $this->authFactory = $authFactory;
        $this->config = $config;
        $this->managementFactory = $managementFactory;
        $this->tokenVerifierFactory = $tokenVerifierFactory;
    }

    /**
     * Create user for given data.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException
     */
    public function createUser(IdentityUserInterface $user): IdentityUserInterface
    {
        $data = $this->getIdentityToArray($user);
        $data['connection'] = $this->config->getConnection();

        $response = $this->managementFactory->create()->users->create($data);

        if (empty($response['user_id']) === false) {
            $this->setIdentityUserId($user, $response['user_id']);

            return $user;
        }

        throw new InvalidResponseFromIdentityException('Missing "user_id" from identity response');
    }

    /**
     * Validate and decode given token and return decoded version.
     *
     * @param string $token
     *
     * @return mixed
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\CoreException
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     */
    public function decodeToken(string $token)
    {
        return $this->tokenVerifierFactory->create()->verifyAndDecode($token);
    }

    /**
     * Delete user for given id.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return void
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\NoIdentityUserIdException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function deleteUser(IdentityUserInterface $user): void
    {
        $this->managementFactory->create()->users->delete($this->getIdentityUserId($user));
    }

    /**
     * Get user information for given id.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\NoIdentityUserIdException
     */
    public function getUser(IdentityUserInterface $user): IdentityUserInterface
    {
        $identityUser = $this->managementFactory->create()->users->get($this->getIdentityUserId($user));

        foreach ($identityUser as $key => $value) {
            $this->setIdentityValue($user, $key, $value);
        }

        return $user;
    }

    /**
     * Login given user.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function loginUser(IdentityUserInterface $user): IdentityUserInterface
    {
        $data = $this->getIdentityToArray($user);
        $data['realm'] = $this->config->getConnection();

        try {
            return $this->authFactory->create()->oauth_token($data);
        } catch (RequestException $exception) {
            throw new LoginFailedException($this->getLoginExceptionMessage($exception));
        }
    }

    /**
     * Update user for given id with given data.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param mixed[] $data
     *
     * @return \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\NoIdentityUserIdException
     */
    public function updateUser(IdentityUserInterface $user, array $data): IdentityUserInterface
    {
        $data['client_id'] = $this->config->getClientId();
        $data['connection'] = $this->config->getConnection();

        $identity = $this->managementFactory->create()->users->update($this->getIdentityUserId($user), $data);

        foreach ($identity as $key => $value) {
            $this->setIdentityValue($user, $key, $value);
        }

        return $user;
    }

    /**
     * Get service name.
     *
     * @return string
     */
    protected function getServiceName(): string
    {
        return IdentityServiceNamesInterface::SERVICE_AUTH0;
    }

    /**
     * Get message for given request exception when trying to login.
     *
     * @param \GuzzleHttp\Exception\RequestException $exception
     *
     * @return string
     */
    private function getLoginExceptionMessage(RequestException $exception): string
    {
        $response = $exception->getResponse();

        if ($response === null) {
            return $exception->getMessage();
        }

        $contents = \json_decode($response->getBody()->getContents(), true) ?? [];

        return $contents['error_description'] ?? $exception->getMessage();
    }
}

\class_alias(
    Auth0IdentityService::class,
    'StepTheFkUp\EasyIdentity\Implementations\Auth0\Auth0IdentityService',
    false
);
