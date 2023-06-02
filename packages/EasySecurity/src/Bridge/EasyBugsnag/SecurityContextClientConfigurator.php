<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\EasyBugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasySecurity\Interfaces\Authorization\PermissionInterface;
use EonX\EasySecurity\Interfaces\Authorization\RoleInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use EonX\EasyUtils\Helpers\ErrorDetailsHelper;

final class SecurityContextClientConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private readonly SecurityContextResolverInterface $securityContextResolver,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                try {
                    $securityContext = $this->securityContextResolver->resolveContext();
                } catch (\Throwable $throwable) {
                    $report->setMetaData([
                        'security' => [
                            'message' => 'Error thrown during security context resolution',
                            'error_details' => ErrorDetailsHelper::resolveSimpleDetails($throwable),
                        ],
                    ]);

                    return;
                }

                $security = [];

                $token = $securityContext->getToken();
                if ($token !== null) {
                    $security['token'] = $this->formatToken($token);
                }

                if (\count($securityContext->getRoles()) > 0) {
                    $security['roles'] = $this->formatRoles($securityContext->getRoles());
                }

                if (\count($securityContext->getPermissions()) > 0) {
                    $security['permissions'] = $this->formatPermissions($securityContext->getPermissions());
                }

                $provider = $securityContext->getProvider();
                if ($provider !== null) {
                    $security['provider'] = $this->formatProvider($provider);
                }

                $user = $securityContext->getUser();
                if ($user !== null) {
                    $security['user'] = $this->formatUser($user);
                }

                $report->setMetaData([
                    'security' => $security,
                ]);
            }));
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[] $permissions
     *
     * @return mixed[]
     */
    private function formatPermissions(array $permissions): array
    {
        $map = static function (PermissionInterface $permission): string {
            return (string)$permission;
        };

        return \array_values(\array_map($map, $permissions));
    }

    /**
     * @return mixed[]
     */
    private function formatProvider(ProviderInterface $provider): array
    {
        return [
            'class' => \get_class($provider),
            'id' => $provider->getUniqueId(),
        ];
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[] $roles
     *
     * @return mixed[]
     */
    private function formatRoles(array $roles): array
    {
        $map = static function (RoleInterface $role): string {
            return (string)$role;
        };

        return \array_values(\array_map($map, $roles));
    }

    /**
     * @return mixed[]
     */
    private function formatToken(ApiTokenInterface $apiToken): array
    {
        return [
            'class' => \get_class($apiToken),
            'original' => $apiToken->getOriginalToken(),
        ];
    }

    /**
     * @return mixed[]
     */
    private function formatUser(UserInterface $user): array
    {
        return [
            'class' => \get_class($user),
            'id' => $user->getUserIdentifier(),
        ];
    }
}
