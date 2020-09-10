<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Implementations\Auth0;

use Auth0\SDK\API\Management;
use Auth0\SDK\API\Management\Users;
use Auth0\SDK\JWTVerifier;
use Closure;
use EonX\EasyIdentity\Exceptions\InvalidResponseFromIdentityException;
use EonX\EasyIdentity\Exceptions\LoginFailedException;
use EonX\EasyIdentity\Exceptions\NoIdentityUserIdException;
use EonX\EasyIdentity\Implementations\Auth0\Auth0IdentityService;
use EonX\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory;
use EonX\EasyIdentity\Implementations\Auth0\Config;
use EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory;
use EonX\EasyIdentity\Implementations\Auth0\TokenVerifierFactory;
use EonX\EasyIdentity\Implementations\IdentityUserService;
use EonX\EasyIdentity\Interfaces\IdentityServiceNamesInterface;
use EonX\EasyIdentity\Interfaces\IdentityUserServiceInterface;
use EonX\EasyIdentity\Tests\AbstractTestCase;
use EonX\EasyIdentity\Tests\Implementations\Stubs\IdentityUserStub;
use GuzzleHttp\Exception\RequestException;
use Mockery\LegacyMockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Auth0IdentityServiceTest extends AbstractTestCase
{
    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    public function testCreateUserInvalidResponseException(): void
    {
        $this->expectException(InvalidResponseFromIdentityException::class);

        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (LegacyMockInterface $mock): void {
            $mock
                ->shouldReceive('create')
                ->once()
                ->with(['connection' => $this->getConfig()->getConnection()])
                ->andReturn(['expected']);
        });

        $identityUserService = new IdentityUserService();
        $identityUser = new IdentityUserStub();

        $this->getServiceForUsersMethod($identityUserService, $management)->createUser($identityUser);
    }

    public function testCreateUserSuccessfully(): void
    {
        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(function (LegacyMockInterface $mock): void {
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

        $identityUserService = new IdentityUserService();
        $identityUser = new IdentityUserStub();

        $this->getServiceForUsersMethod($identityUserService, $management)->createUser($identityUser);

        self::assertEquals(
            'identity-id',
            $identityUserService->getIdentityUserId($identityUser, IdentityServiceNamesInterface::SERVICE_AUTH0)
        );
    }

    public function testDecodeToken(): void
    {
        /** @var \EonX\EasyIdentity\Implementations\Auth0\TokenVerifierFactory $tokenVerifierFactory */
        $tokenVerifierFactory = $this->mock(TokenVerifierFactory::class, function (LegacyMockInterface $mock): void {
            $jwt = $this->mock(JWTVerifier::class, static function (LegacyMockInterface $mock): void {
                $mock->shouldReceive('verifyAndDecode')->once()->with('token')->andReturn(['expected']);
            });

            $mock->shouldReceive('create')->once()->withNoArgs()->andReturn($jwt);
        });
        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            new AuthenticationApiClientFactory($this->getConfig()),
            $this->getConfig(),
            new IdentityUserService(),
            $management,
            $tokenVerifierFactory
        );

        self::assertEquals(['expected'], $service->decodeToken('token'));
    }

    public function testDeleteUser(): void
    {
        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(static function (LegacyMockInterface $mock): void {
            $mock->shouldReceive('delete')->once()->withArgs(static function ($userId): bool {
                $condition = \is_string($userId) && $userId === 'identity-id';

                self::assertTrue($condition);

                return $condition;
            })->andReturnNull();
        });

        $identityUserService = new IdentityUserService();
        $identityUser = new IdentityUserStub();

        $identityUserService->setIdentityUserId(
            $identityUser,
            IdentityServiceNamesInterface::SERVICE_AUTH0,
            'identity-id'
        );

        $this->getServiceForUsersMethod($identityUserService, $management)->deleteUser($identityUser);
    }

    public function testGetUser(): void
    {
        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(static function (LegacyMockInterface $mock): void {
            $mock->shouldReceive('get')->once()->withArgs(static function ($userId): bool {
                $condition = \is_string($userId) && $userId === 'identity-id';

                self::assertTrue($condition);

                return $condition;
            })->andReturn(['new-key' => 'new-value']);
        });

        $service = IdentityServiceNamesInterface::SERVICE_AUTH0;

        $identityUserService = new IdentityUserService();
        $identityUser = new IdentityUserStub();
        $identityUserService->setIdentityUserId($identityUser, $service, 'identity-id');

        $this->getServiceForUsersMethod($identityUserService, $management)->getUser($identityUser);

        self::assertEquals(
            ['new-key' => 'new-value'],
            $identityUserService->getIdentityToArray($identityUser, $service)
        );
    }

    public function testGetUserNoIdentityUserIdException(): void
    {
        $this->expectException(NoIdentityUserIdException::class);

        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(static function (LegacyMockInterface $mock): void {
            $mock->shouldNotReceive('get');
        });

        $identityUserService = new IdentityUserService();
        $identityUser = new IdentityUserStub();

        $this->getServiceForUsersMethod($identityUserService, $management)->getUser($identityUser);
    }

    public function testLoginUserWithExceptionAuthResponse(): void
    {
        $this->expectException(LoginFailedException::class);

        /** @var \EonX\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory */
        $authFactory = $this->mock(AuthenticationApiClientFactory::class, function (LegacyMockInterface $mock): void {
            $exception = $this->mock(RequestException::class, function (LegacyMockInterface $mock): void {
                $response = $this->mock(ResponseInterface::class, function (LegacyMockInterface $mock): void {
                    $body = $this->mock(StreamInterface::class, static function (LegacyMockInterface $mock): void {
                        $mock->shouldReceive('getContents')->once()->withNoArgs()->andReturn('');
                    });

                    $mock->shouldReceive('getBody')->once()->withNoArgs()->andReturn($body);
                });

                $mock->shouldReceive('getResponse')->once()->withNoArgs()->andReturn($response);
            });

            $mock->shouldReceive('create')->once()->withNoArgs()->andThrow($exception);
        });

        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            $authFactory,
            $this->getConfig(),
            new IdentityUserService(),
            $management,
            new TokenVerifierFactory($this->getConfig())
        );

        $identityUser = new IdentityUserStub();

        $service->loginUser($identityUser);
    }

    public function testLoginUserWithExceptionNullAuthResponse(): void
    {
        $this->expectException(LoginFailedException::class);

        /** @var \EonX\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory $authFactory */
        $authFactory = $this->mock(AuthenticationApiClientFactory::class, function (LegacyMockInterface $mock): void {
            $exception = $this->mock(RequestException::class, static function (LegacyMockInterface $mock): void {
                $mock->shouldReceive('getResponse')->once()->withNoArgs()->andReturn(null);
            });
            $mock->shouldReceive('create')->once()->withNoArgs()->andThrow($exception);
        });

        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mock(ManagementApiClientFactory::class);

        $service = new Auth0IdentityService(
            $authFactory,
            $this->getConfig(),
            new IdentityUserService(),
            $management,
            new TokenVerifierFactory($this->getConfig())
        );

        $identityUser = new IdentityUserStub();

        $service->loginUser($identityUser);
    }

    public function testUpdateUser(): void
    {
        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory $management */
        $management = $this->mockManagementForUsersClient(static function (LegacyMockInterface $mock): void {
            $mock
                ->shouldReceive('update')
                ->once()
                ->withArgs(static function ($identityId, $identityArray): bool {
                    return \is_string($identityId)
                        && \array_key_exists('email', $identityArray)
                        && \array_key_exists('client_id', $identityArray)
                        && \array_key_exists('connection', $identityArray);
                })
                ->andReturn(['new-key' => 'new-value']);
        });

        $service = IdentityServiceNamesInterface::SERVICE_AUTH0;

        $identityUserService = new IdentityUserService();
        $identityUser = new IdentityUserStub();
        $identityUserService->setIdentityUserId($identityUser, $service, 'my-identity-id');
        $identityUserService->setIdentityValue($identityUser, $service, 'email', 'email@email.com');

        $this->getServiceForUsersMethod($identityUserService, $management)->updateUser(
            $identityUser,
            $identityUserService->getIdentityToArray($identityUser, $service)
        );

        $expected = [
            'email' => 'email@email.com',
            'new-key' => 'new-value',
        ];

        self::assertEquals($expected, $identityUserService->getIdentityToArray($identityUser, $service));
    }

    private function getConfig(): Config
    {
        if ($this->config !== null) {
            return $this->config;
        }

        return $this->config = new Config([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain',
        ]);
    }

    private function getServiceForUsersMethod(
        IdentityUserServiceInterface $identityUserService,
        ManagementApiClientFactory $management
    ): Auth0IdentityService {
        return new Auth0IdentityService(
            new AuthenticationApiClientFactory($this->getConfig()),
            $this->getConfig(),
            $identityUserService,
            $management,
            new TokenVerifierFactory($this->getConfig())
        );
    }

    private function mockManagementForUsersClient(Closure $closure): LegacyMockInterface
    {
        $users = $this->mock(Users::class, $closure);

        return $this->mock(
            ManagementApiClientFactory::class,
            function (LegacyMockInterface $mock) use ($users): void {
                $management = $this->mock(Management::class);
                $management->users = $users;

                $mock->shouldReceive('create')->once()->withNoArgs()->andReturn($management);
            }
        );
    }
}
