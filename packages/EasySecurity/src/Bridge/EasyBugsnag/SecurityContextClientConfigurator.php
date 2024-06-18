<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\EasyBugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyBugsnag\Configurator\AbstractClientConfigurator;
use EonX\EasySecurity\Interfaces\Authorization\PermissionInterface;
use EonX\EasySecurity\Interfaces\Authorization\RoleInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use EonX\EasyUtils\Helpers\ErrorDetailsHelper;
use Throwable;

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
                } catch (Throwable $throwable) {
                    $report->setMetaData([
                        'security' => [
                            'error_details' => ErrorDetailsHelper::resolveSimpleDetails($throwable),
                            'message' => 'Error thrown during security context resolution',
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
     */
    private function formatPermissions(array $permissions): array
    {
        $map = static fn (PermissionInterface $permission): string => (string)$permission;

        return \array_values(\array_map($map, $permissions));
    }

    private function formatProvider(ProviderInterface $provider): array
    {
        return [
            'class' => $provider::class,
            'id' => $provider->getUniqueId(),
        ];
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[] $roles
     */
    private function formatRoles(array $roles): array
    {
        $map = static fn (RoleInterface $role): string => (string)$role;

        return \array_values(\array_map($map, $roles));
    }

    private function formatToken(ApiTokenInterface $apiToken): array
    {
        return [
            'class' => $apiToken::class,
            'original' => $apiToken->getOriginalToken(),
        ];
    }

    private function formatUser(UserInterface $user): array
    {
        return [
            'class' => $user::class,
            'id' => $user->getUserIdentifier(),
        ];
    }
}
