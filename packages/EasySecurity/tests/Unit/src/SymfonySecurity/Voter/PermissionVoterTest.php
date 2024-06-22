<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Voter;

use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProvider;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasySecurity\SymfonySecurity\Voter\PermissionVoter;
use EonX\EasySecurity\Tests\Stub\Resolver\SecurityContextResolverStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class PermissionVoterTest extends AbstractUnitTestCase
{
    /**
     * @see testVoter
     */
    public static function providerTestVoter(): iterable
    {
        yield 'Abstain because permission not in matrix' => [
            new AuthorizationMatrixProvider([], []),
            new SecurityContext(),
            'permission',
            VoterInterface::ACCESS_ABSTAIN,
        ];

        yield 'Denied because permission not on context' => [
            new AuthorizationMatrixProvider([], ['permission']),
            new SecurityContext(),
            'permission',
            VoterInterface::ACCESS_DENIED,
        ];

        $securityContext = new SecurityContext();
        $securityContext->addPermissions(['permission']);

        yield 'Granted because permission in matrix and on context' => [
            new AuthorizationMatrixProvider([], ['permission']),
            $securityContext,
            'permission',
            VoterInterface::ACCESS_GRANTED,
        ];
    }

    #[DataProvider('providerTestVoter')]
    public function testVoter(
        AuthorizationMatrixProviderInterface $authorizationMatrix,
        SecurityContextInterface $securityContext,
        string $permission,
        int $expectedVote,
    ): void {
        $securityContext->setAuthorizationMatrix($authorizationMatrix);

        $voter = new PermissionVoter(new SecurityContextResolverStub($securityContext));
        $token = new NullToken();

        self::assertEquals($expectedVote, $voter->vote($token, null, [$permission]));
    }
}
