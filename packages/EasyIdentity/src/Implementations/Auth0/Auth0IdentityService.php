<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Implementations\Auth0;

use GuzzleHttp\Exception\RequestException;
use StepTheFkUp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException;
use StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException;
use StepTheFkUp\EasyIdentity\Implementations\AbstractIdentityService;
use StepTheFkUp\EasyIdentity\Interfaces\IdentityServiceInterface;
use StepTheFkUp\EasyIdentity\Interfaces\IdentityServiceNamesInterface;
use StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface;

final class Auth0IdentityService extends AbstractIdentityService implements IdentityServiceInterface
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
     * Create user for given data.
     *
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException
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
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return void
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\NoIdentityUserIdException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function deleteUser(IdentityUserInterface $user): void
    {
        $this->managementFactory->create()->users->delete($this->getIdentityUserId($user));
    }

    /**
     * Get user information for given id.
     *
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\NoIdentityUserIdException
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
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function loginUser(IdentityUserInterface $user): IdentityUserInterface
    {
        $data = $this->getIdentityToArray($user);
        $data['realm'] = $this->config->getConnection();

        try {
            return $this->authFactory->create()->login($data);
        } catch (RequestException $exception) {
            throw new LoginFailedException($this->getLoginExceptionMessage($exception));
        }
    }

    /**
     * Update user for given id with given data.
     *
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param mixed[] $data
     *
     * @return \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\NoIdentityUserIdException
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
