<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Implementations\Auth0;

use GuzzleHttp\Exception\RequestException;
use StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException;
use StepTheFkUp\EasyIdentity\Interfaces\IdentityServiceInterface;
use StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdResolverInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Suppress due to dependency
 */
class Auth0IdentityService implements IdentityServiceInterface
{
    /**
     * @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory
     */
    private $authFactory;

    /**
     * @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory
     */
    private $managementFactory;

    /**
     * @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory
     */
    private $tokenVerifierFactory;

    /**
     * Auth0IdentityService constructor.
     *
     * @param \StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory
     * @param \StepTheFkUp\EasyIdentity\Implementations\Auth0\Config $config
     * @param \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $managementFactory
     * @param \StepTheFkUp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory $tokenVerifierFactory
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
     * Create user for given email and password.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(array $data): array
    {
        $data['connection'] = $this->config->getConnection();

        return $this->managementFactory->create()->users->create($data);
    }

    /**
     * Validate and decode given token and return decoded version.
     *
     * @param string $token
     *
     * @return mixed
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
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
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdResolverInterface $userIdResolver
     *
     * @return void
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function deleteUser(IdentityUserIdResolverInterface $userIdResolver): void
    {
        $this->managementFactory->create()->users->delete($userIdResolver->getUserId());
    }

    /**
     * Get user information for given id.
     *
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdResolverInterface $userIdResolver
     *
     * @return mixed[]
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function getUser(IdentityUserIdResolverInterface $userIdResolver): array
    {
        return $this->managementFactory->create()->users->get($userIdResolver->getUserId());
    }

    /**
     * Login user for given email and password.
     *
     * @param mixed[] $data
     *
     * @return mixed
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function loginUser(array $data)
    {
        $data['realm'] = $this->config->getConnection();
        $data['metadata'] = $data['metadata'] ?? [];
        $data['metadata']['token_version'] = 'v1.0.0';
        $data['metadataDomain'] = 'https://edining.com.au';

        try {
            return $this->authFactory->create()->login($data);
        } catch (RequestException $exception) {
            throw new LoginFailedException($this->getLoginExceptionMessage($exception));
        }
    }

    /**
     * Login user for given username and password.
     *
     * @param string $username
     * @param string $password
     * @param null|mixed[] $data
     *
     * @return mixed
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function loginUserWithUsernamePassword(string $username, string $password, ?array $data = null)
    {
        $data = $data ?? [];
        $data['username'] = $username;
        $data['password'] = $password;

        return $this->loginUser($data);
    }

    /**
     * Update user for given id with given data.
     *
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdResolverInterface $userIdResolver
     * @param mixed[] $data
     *
     * @return mixed[]
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function updateUser(IdentityUserIdResolverInterface $userIdResolver, array $data): array
    {
        $data['client_id'] = $this->config->getClientId();
        $data['connection'] = $this->config->getConnection();

        return $this->managementFactory->create()->users->update($userIdResolver->getUserId(), $data);
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
