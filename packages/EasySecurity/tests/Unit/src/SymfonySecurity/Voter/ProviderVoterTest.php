<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Voter;

use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasySecurity\SymfonySecurity\Voter\ProviderVoter;
use EonX\EasySecurity\Tests\Stub\Entity\ProviderRestrictedStub;
use EonX\EasySecurity\Tests\Stub\Entity\ProviderStub;
use EonX\EasySecurity\Tests\Stub\Resolver\SecurityContextResolverStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ProviderVoterTest extends AbstractUnitTestCase
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
        $securityContext->setProvider(new ProviderStub('different-provider-id'));

        yield 'Denied because provider on context different than restricted provider' => [
            $securityContext,
            new ProviderRestrictedStub('provider-id'),
            VoterInterface::ACCESS_DENIED,
        ];
    }

    #[DataProvider('providerTestVoter')]
    public function testVoter(SecurityContextInterface $securityContext, mixed $subject, int $expectedVote): void
    {
        $voter = new ProviderVoter(new SecurityContextResolverStub($securityContext));
        $token = new NullToken();

        self::assertEquals($expectedVote, $voter->vote($token, $subject, ['attr']));
    }
}
