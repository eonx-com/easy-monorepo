<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Voter;

use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProvider;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasySecurity\SymfonySecurity\Voter\RoleVoter;
use EonX\EasySecurity\Tests\Stub\Resolver\SecurityContextResolverStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class RoleVoterTest extends AbstractUnitTestCase
{
    /**
     * @see testVoter
     */
    public static function provideVoterData(): iterable
    {
        yield 'Abstain because role not in matrix' => [
            new AuthorizationMatrixProvider([], []),
            new SecurityContext(),
            'role',
            VoterInterface::ACCESS_ABSTAIN,
        ];

        yield 'Denied because role not on context' => [
            new AuthorizationMatrixProvider(['role'], []),
            new SecurityContext(),
            'role',
            VoterInterface::ACCESS_DENIED,
        ];

        $authorizationMatrix = new AuthorizationMatrixProvider(['role'], []);
        $securityContext = new SecurityContext();
        $securityContext->setAuthorizationMatrix($authorizationMatrix);
        $securityContext->addRoles(['role']);

        yield 'Granted because role in matrix and on context' => [
            $authorizationMatrix,
            $securityContext,
            'role',
            VoterInterface::ACCESS_GRANTED,
        ];
    }

    #[DataProvider('provideVoterData')]
    public function testVoter(
        AuthorizationMatrixProviderInterface $authorizationMatrix,
        SecurityContextInterface $securityContext,
        string $role,
        int $expectedVote,
    ): void {
        $securityContext->setAuthorizationMatrix($authorizationMatrix);

        $voter = new RoleVoter(new SecurityContextResolverStub($securityContext));
        $token = new NullToken();

        self::assertEquals($expectedVote, $voter->vote($token, null, [$role]));
    }
}
