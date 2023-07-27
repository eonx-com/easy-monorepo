<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Security\Voters;

use EonX\EasySecurity\Authorization\AuthorizationMatrix;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\RoleVoter;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\SecurityContext;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\SecurityContextResolverStub;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class RoleVoterTest extends AbstractTestCase
{
    /**
     * @see testVoter
     */
    public static function providerTestVoter(): iterable
    {
        yield 'Abstain because role not in matrix' => [
            new AuthorizationMatrix([], []),
            new SecurityContext(),
            'role',
            VoterInterface::ACCESS_ABSTAIN,
        ];

        yield 'Denied because role not on context' => [
            new AuthorizationMatrix(['role'], []),
            new SecurityContext(),
            'role',
            VoterInterface::ACCESS_DENIED,
        ];

        $authorizationMatrix = new AuthorizationMatrix(['role'], []);
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

    /**
     * @dataProvider providerTestVoter
     */
    public function testVoter(
        AuthorizationMatrixInterface $authorizationMatrix,
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
