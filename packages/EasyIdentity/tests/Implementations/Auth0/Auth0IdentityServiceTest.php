<?php
declare(strict_types=1);

namespace Tests\App\Unit\Services\Identity\Auth0;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;
use Auth0\SDK\API\Management\Users;
use Auth0\SDK\JWTVerifier;
use Closure;
use GuzzleHttp\Exception\RequestException;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use StepTheFkUp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException;
use StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\Auth0IdentityService;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\Config;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory;
use StepTheFkUp\EasyIdentity\Tests\AbstractTestCase;
use StepTheFkUp\EasyIdentity\Tests\Implementations\Stubs\IdentityUserIdHolderStub;

class Auth0IdentityServiceTest extends AbstractTestCase
{
    /**
     * @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreateUserInvalidResponseException(): void
    {
        $this->expectException(InvalidResponseFromIdentityException::class);

        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock
                ->shouldReceive('create')
                ->once()
                ->with(['connection' => $this->getConfig()->getConnection()])
                ->andReturn(['expected']);
        });

        $this->getServiceForUsersMethod($management)->createUser(new IdentityUserIdHolderStub('initial'), []);
    }
    /**
     * Service should call Auth0 to create user for given data.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreateUserSuccessfully(): void
    {
        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock
                ->shouldReceive('create')
                ->once()
                ->withArgs(
                    function ($connection): bool {
                        return \in_array('connection', $connection, true)
                            && \array_shift($connection) === $this->getConfig()->getConnection();
                    }
                )
                ->andReturn(['user_id' => 'identity-id', 'expected']);
        });

        $identityUserIdHolder = new IdentityUserIdHolderStub('initial');

        $actual = $this->getServiceForUsersMethod($management)->createUser($identityUserIdHolder, []);

        self::assertEquals(['user_id' => 'identity-id', 'expected'], $actual);
        self::assertEquals('identity-id', $identityUserIdHolder->getIdentityUserId());
    }

    /**
     * Service should call token verifier to validate the given token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\CoreException
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     */
    public function testDecodeToken(): void
    {
        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory $tokenVerifierFactory */
        $tokenVerifierFactory = $this->mock(TokenVerifierFactory::class, function (MockInterface $mock): void {
            $jwt = $this->mock(JWTVerifier::class, function (MockInterface $mock): void {
                $mock->shouldReceive('verifyAndDecode')->once()->with('token')->andReturn(['expected']);
            });

            $mock->shouldReceive('create')->once()->withNoArgs()->andReturn($jwt);
        });
        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            new AuthenticationApiClientFactory($this->getConfig()),
            $this->getConfig(),
            $management,
            $tokenVerifierFactory
        );

        self::assertEquals(['expected'], $service->decodeToken('token'));
    }

    /**
     * Service should call Auth0 to delete user for given id.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testDeleteUser(): void
    {
        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock->shouldReceive('delete')->once()->withArgs(function ($userId): bool {
                $condition = \is_string($userId) && $userId === 'identity-id';

                self::assertTrue($condition);

                return $condition;
            })->andReturnNull();
        });

        $this->getServiceForUsersMethod($management)->deleteUser(new IdentityUserIdHolderStub('identity-id'));
    }

    /**
     * Service should call Auth0 to get user for given id.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testGetUser(): void
    {
        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock->shouldReceive('get')->once()->withArgs(function ($userId): bool {
                $condition = \is_string($userId) && $userId === 'identity-id';

                self::assertTrue($condition);

                return $condition;
            })->andReturn([]);
        });

        $this->getServiceForUsersMethod($management)->getUser(new IdentityUserIdHolderStub('identity-id'));
    }

    /**
     * Test login user with exception and response content from auth api.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function testLoginUserWithExceptionAuthResponse(): void
    {
        $this->expectException(LoginFailedException::class);

        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory */
        $authFactory = $this->mock(AuthenticationApiClientFactory::class, function (MockInterface $mock): void {
            $exception = $this->mock(RequestException::class, function (MockInterface $mock): void {
                $response = $this->mock(ResponseInterface::class, function (MockInterface $mock): void {
                    $body = $this->mock(StreamInterface::class, function (MockInterface $mock): void {
                        $mock->shouldReceive('getContents')->once()->withNoArgs()->andReturn('');
                    });

                    $mock->shouldReceive('getBody')->once()->withNoArgs()->andReturn($body);
                });

                $mock->shouldReceive('getResponse')->once()->withNoArgs()->andReturn($response);
            });

            $mock->shouldReceive('create')->once()->withNoArgs()->andThrow($exception);
        });

        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            $authFactory,
            $this->getConfig(),
            $management,
            new TokenVerifierFactory($this->getConfig())
        );

        $service->loginUser([]);
    }

    /**
     * Test login user with exception and no response from auth api.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function testLoginUserWithExceptionNullAuthResponse(): void
    {
        $this->expectException(LoginFailedException::class);

        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory */
        $authFactory = $this->mock(AuthenticationApiClientFactory::class, function (MockInterface $mock): void {
            $exception = $this->mock(RequestException::class, function (MockInterface $mock): void {
                $mock->shouldReceive('getResponse')->once()->withNoArgs()->andReturn(null);
            });
            $mock->shouldReceive('create')->once()->withNoArgs()->andThrow($exception);
        });

        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            $authFactory,
            $this->getConfig(),
            $management,
            new TokenVerifierFactory($this->getConfig())
        );

        $service->loginUser([]);
    }

    /**
     * Service should call auth0 authentication api to login a user.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function testLoginUserWithUsernamePassword(): void
    {
        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authentication */
        $authentication = $this->mock(AuthenticationApiClientFactory::class, function (MockInterface $mock): void {
            $auth = $this->mock(Authentication::class, function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('login')
                    ->once()
                    ->with([
                        'username' => 'username',
                        'password' => 'password',
                        'realm' => $this->getConfig()->getConnection(),
                        'metadata' => ['token_version' => 'v1.0.0'],
                        'metadataDomain' => 'https://edining.com.au'
                    ])
                    ->andReturn(['expected']);
            });

            $mock->shouldReceive('create')->once()->withNoArgs()->andReturn($auth);
        });

        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            $authentication,
            $this->getConfig(),
            $management,
            new TokenVerifierFactory($this->getConfig())
        );

        self::assertEquals(['expected'], $service->loginUserWithUsernamePassword('username', 'password'));
    }

    /**
     * Service should call Auth0 to update user for given id and data.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdateUser(): void
    {
        /** @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock
                ->shouldReceive('update')
                ->once()
                ->withArgs(function ($identityId, $identityArray): bool {
                    return \is_string($identityId)
                        && \array_key_exists('email', $identityArray)
                        && \array_key_exists('client_id', $identityArray)
                        && \array_key_exists('connection', $identityArray);
                })
                ->andReturn(['expected']);
        });

        $actual = $this->getServiceForUsersMethod($management)->updateUser(new IdentityUserIdHolderStub(
            'identity-id'), [
            'email' => 'email@email.com'
        ]);

        self::assertEquals(['expected'], $actual);
    }

    /**
     * Get config.
     *
     * @return \StepTheFkUp\EasyIdentity\Implementations\Auth0\Config
     */
    private function getConfig(): Config
    {
        if ($this->config !== null) {
            return $this->config;
        }

        return $this->config = new Config([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain'
        ]);
    }

    /**
     * Instantiate service for simple users method test.
     *
     * @param \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management
     *
     * @return \StepTheFkUp\EasyIdentity\Implementations\Auth0\Auth0IdentityService
     */
    private function getServiceForUsersMethod(ManagementApiClientFactory $management): Auth0IdentityService
    {
        return new Auth0IdentityService(
            new AuthenticationApiClientFactory($this->getConfig()),
            $this->getConfig(),
            $management,
            new TokenVerifierFactory($this->getConfig())
        );
    }

    /**
     * Mock management factory to return users with expectations from given closure.
     *
     * @param \Closure $closure
     *
     * @return \Mockery\MockInterface
     */
    private function mockManagementForUsersClient(Closure $closure): MockInterface
    {
        $users = $this->mock(Users::class, $closure);

        return $this->mock(
            ManagementApiClientFactory::class,
            function (MockInterface $mock) use ($users): void {
                $management = $this->mock(Management::class);
                $management->users = $users;

                $mock->shouldReceive('create')->once()->withNoArgs()->andReturn($management);
            }
        );
    }
}
