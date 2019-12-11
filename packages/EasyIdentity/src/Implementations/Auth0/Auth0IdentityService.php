<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations\Auth0;

use GuzzleHttp\Exception\RequestException;
use EonX\EasyIdentity\Exceptions\InvalidResponseFromIdentityException;
use EonX\EasyIdentity\Exceptions\LoginFailedException;
use EonX\EasyIdentity\Implementations\AbstractIdentityService;
use EonX\EasyIdentity\Interfaces\IdentityServiceNamesInterface;
use EonX\EasyIdentity\Interfaces\IdentityUserInterface;
use EonX\EasyIdentity\Interfaces\IdentityUserServiceInterface;

final class Auth0IdentityService extends AbstractIdentityService
{
    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory
     */
    private $authFactory;

    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory
     */
    private $managementFactory;

    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\TokenVerifierFactory
     */
    private $tokenVerifierFactory;

    /**
     * Auth0IdentityService constructor.
     *
     * @param \EonX\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory
     * @param \EonX\EasyIdentity\Implementations\Auth0\Config $config
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserServiceInterface $identityUserService
     * @param \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $managementFactory
     * @param \EonX\EasyIdentity\Implementations\Auth0\TokenVerifierFactory $tokenVerifierFactory
     */
    public function __construct(
        AuthenticationApiClientFactory $authFactory,
        Config $config,
        IdentityUserServiceInterface $identityUserService,
        ManagementApiClientFactory $managementFactory,
        TokenVerifierFactory $tokenVerifierFactory
    ) {
        parent::__construct($identityUserService);

        $this->authFactory = $authFactory;
        $this->config = $config;
        $this->managementFactory = $managementFactory;
        $this->tokenVerifierFactory = $tokenVerifierFactory;
    }

    /**
     * Create user for given data.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \EonX\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \EonX\EasyIdentity\Exceptions\InvalidResponseFromIdentityException
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
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
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
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return void
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \EonX\EasyIdentity\Exceptions\NoIdentityUserIdException
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function deleteUser(IdentityUserInterface $user): void
    {
        $this->managementFactory->create()->users->delete($this->getIdentityUserId($user));
    }

    /**
     * Get user information for given id.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \EonX\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \EonX\EasyIdentity\Exceptions\NoIdentityUserIdException
     */
    public function getUser(IdentityUserInterface $user): IdentityUserInterface
    {
        $identityUser = $this->managementFactory->create()->users->get($this->getIdentityUserId($user));

        if (\is_array($identityUser) === true) {
            foreach ($identityUser as $key => $value) {
                $this->setIdentityValue($user, $key, $value);
            }
        }

        return $user;
    }

    /**
     * Login given user.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \EonX\EasyIdentity\Interfaces\IdentityUserInterface
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
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param mixed[] $data
     *
     * @return \EonX\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \EonX\EasyIdentity\Exceptions\NoIdentityUserIdException
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

        return isset($contents['error_description']) === true && \is_scalar($contents['error_description']) === true ?
            (string)$contents['error_description'] :
            $exception->getMessage();
    }
}


