<?php
declare(strict_types=1);

namespace EonX\EasySecurity\EasyBugsnag\Configurator;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use EonX\EasyBugsnag\Configurator\AbstractClientConfigurator;
use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Authorization\ValueObject\Role;
use EonX\EasySecurity\Common\Entity\ProviderInterface;
use EonX\EasySecurity\Common\Entity\UserInterface;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use EonX\EasyUtils\Common\Helper\ErrorDetailsHelper;
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
     * @param \EonX\EasySecurity\Authorization\ValueObject\Permission[] $permissions
     */
    private function formatPermissions(array $permissions): array
    {
        $map = static fn (Permission $permission): string => (string)$permission;

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
     * @param \EonX\EasySecurity\Authorization\ValueObject\Role[] $roles
     */
    private function formatRoles(array $roles): array
    {
        $map = static fn (Role $role): string => (string)$role;

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
