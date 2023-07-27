<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Security\Voters;

use EonX\EasySecurity\Bridge\Symfony\Security\Voters\ProviderVoter;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\SecurityContext;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\ProviderInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\ProviderRestrictedStub;
use EonX\EasySecurity\Tests\Stubs\SecurityContextResolverStub;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ProviderVoterTest extends AbstractTestCase
{
    /**
     * @see testVoter
     */
    public static function providerTestVoter(): iterable
    {
        yield 'Abstain because subject not provider restricted' => [
            new SecurityContext(),
            [],
            VoterInterface::ACCESS_ABSTAIN,
        ];

        yield 'Denied because no provider on context' => [
            new SecurityContext(),
            new ProviderRestrictedStub('provider-id'),
            VoterInterface::ACCESS_DENIED,
        ];

        $securityContext = new SecurityContext();
        $securityContext->setProvider(new ProviderInterfaceStub('different-provider-id'));

        yield 'Denied because provider on context different than restricted provider' => [
            $securityContext,
            new ProviderRestrictedStub('provider-id'),
            VoterInterface::ACCESS_DENIED,
        ];
    }

    /**
     * @dataProvider providerTestVoter
     */
    public function testVoter(SecurityContextInterface $securityContext, mixed $subject, int $expectedVote): void
    {
        $voter = new ProviderVoter(new SecurityContextResolverStub($securityContext));
        $token = new NullToken();

        self::assertEquals($expectedVote, $voter->vote($token, $subject, ['attr']));
    }
}
