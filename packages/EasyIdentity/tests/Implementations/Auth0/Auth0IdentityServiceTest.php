<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Tests\Implementations\Auth0;

use Auth0\SDK\API\Management;
use Auth0\SDK\API\Management\Users;
use Auth0\SDK\JWTVerifier;
use Closure;
use GuzzleHttp\Exception\RequestException;
use LoyaltyCorp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException;
use LoyaltyCorp\EasyIdentity\Exceptions\LoginFailedException;
use LoyaltyCorp\EasyIdentity\Exceptions\NoIdentityUserIdException;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\Auth0IdentityService;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceNamesInterface;
use LoyaltyCorp\EasyIdentity\Tests\AbstractTestCase;
use LoyaltyCorp\EasyIdentity\Tests\Implementations\Stubs\IdentityUserStub;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Auth0IdentityServiceTest extends AbstractTestCase
{
    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * Service should throw exception if response structure is invalid.
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreateUserInvalidResponseException(): void
    {
        $this->expectException(InvalidResponseFromIdentityException::class);

        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock
                ->shouldReceive('create')
                ->once()
                ->with(['connection' => $this->getConfig()->getConnection()])
                ->andReturn(['expected']);
        });

        $this->getServiceForUsersMethod($management)->createUser(new IdentityUserStub());
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
        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
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
                ->andReturn(['user_id' => 'identity-id']);
        });

        $identityUser = new IdentityUserStub();

        $this->getServiceForUsersMethod($management)->createUser($identityUser);

        self::assertEquals(
            'identity-id',
            $identityUser->getIdentityUserId(IdentityServiceNamesInterface::SERVICE_AUTH0)
        );
    }

    /**
     * Service should call token verifier to validate the given token.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\CoreException
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     */
    public function testDecodeToken(): void
    {
        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory $tokenVerifierFactory */
        $tokenVerifierFactory = $this->mock(TokenVerifierFactory::class, function (MockInterface $mock): void {
            $jwt = $this->mock(JWTVerifier::class, function (MockInterface $mock): void {
                $mock->shouldReceive('verifyAndDecode')->once()->with('token')->andReturn(['expected']);
            });

            $mock->shouldReceive('create')->once()->withNoArgs()->andReturn($jwt);
        });
        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
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
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testDeleteUser(): void
    {
        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock->shouldReceive('delete')->once()->withArgs(function ($userId): bool {
                $condition = \is_string($userId) && $userId === 'identity-id';

                self::assertTrue($condition);

                return $condition;
            })->andReturnNull();
        });

        $identityUser = new IdentityUserStub();
        $identityUser->setIdentityUserId(IdentityServiceNamesInterface::SERVICE_AUTH0, 'identity-id');

        $this->getServiceForUsersMethod($management)->deleteUser($identityUser);
    }

    /**
     * Service should call Auth0 to get user for given id.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testGetUser(): void
    {
        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock->shouldReceive('get')->once()->withArgs(function ($userId): bool {
                $condition = \is_string($userId) && $userId === 'identity-id';

                self::assertTrue($condition);

                return $condition;
            })->andReturn(['new-key' => 'new-value']);
        });

        $service = IdentityServiceNamesInterface::SERVICE_AUTH0;
        $identityUser = new IdentityUserStub();
        $identityUser->setIdentityUserId($service, 'identity-id');

        $this->getServiceForUsersMethod($management)->getUser($identityUser);

        self::assertEquals(['new-key' => 'new-value'], $identityUser->getIdentityToArray($service));
    }

    /**
     * Identity service should throw exception if no identity user id on given user.
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetUserNoIdentityUserIdException(): void
    {
        $this->expectException(NoIdentityUserIdException::class);

        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (MockInterface $mock): void {
            $mock->shouldNotReceive('get');
        });

        $this->getServiceForUsersMethod($management)->getUser(new IdentityUserStub());
    }

    /**
     * Test login user with exception and response content from auth api.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\LoginFailedException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function testLoginUserWithExceptionAuthResponse(): void
    {
        $this->expectException(LoginFailedException::class);

        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory */
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

        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            $authFactory,
            $this->getConfig(),
            $management,
            new TokenVerifierFactory($this->getConfig())
        );

        $service->loginUser(new IdentityUserStub());
    }

    /**
     * Test login user with exception and no response from auth api.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\LoginFailedException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function testLoginUserWithExceptionNullAuthResponse(): void
    {
        $this->expectException(LoginFailedException::class);

        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory */
        $authFactory = $this->mock(AuthenticationApiClientFactory::class, function (MockInterface $mock): void {
            $exception = $this->mock(RequestException::class, function (MockInterface $mock): void {
                $mock->shouldReceive('getResponse')->once()->withNoArgs()->andReturn(null);
            });
            $mock->shouldReceive('create')->once()->withNoArgs()->andThrow($exception);
        });

        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            $authFactory,
            $this->getConfig(),
            $management,
            new TokenVerifierFactory($this->getConfig())
        );

        $service->loginUser(new IdentityUserStub());
    }

    /**
     * Service should call Auth0 to update user for given id and data.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdateUser(): void
    {
        /** @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
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
                ->andReturn(['new-key' => 'new-value']);
        });

        $service = IdentityServiceNamesInterface::SERVICE_AUTH0;
        $identityUser = new IdentityUserStub();
        $identityUser->setIdentityUserId($service, 'my-identity-id');
        $identityUser->setIdentityValue($service, 'email', 'email@email.com');

        $this->getServiceForUsersMethod($management)->updateUser(
            $identityUser,
            $identityUser->getIdentityToArray($service)
        );

        $expected = ['email' => 'email@email.com', 'new-key' => 'new-value'];

        self::assertEquals($expected, $identityUser->getIdentityToArray($service));
    }

    /**
     * Get config.
     *
     * @return \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config
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
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management
     *
     * @return \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Auth0IdentityService
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

\class_alias(
    Auth0IdentityServiceTest::class,
    'StepTheFkUp\EasyIdentity\Tests\Implementations\Auth0\Auth0IdentityServiceTest',
    false
);
