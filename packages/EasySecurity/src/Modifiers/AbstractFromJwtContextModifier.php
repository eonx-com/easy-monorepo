<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface;
use EonX\EasySecurity\JwtClaimFetcher;

abstract class AbstractFromJwtContextModifier extends AbstractContextModifier
{
    /**
     * @var string
     */
    private $jwtClaim;

    /**
     * @var \EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface
     */
    private $jwtClaimFetcher;

    public function __construct(
        ?string $jwtClaim = null,
        ?int $priority = null,
        ?JwtClaimFetcherInterface $jwtClaimFetcher = null
    ) {
        if ($jwtClaim === null) {
            @\trigger_error(
                'Not setting $jwtClaim is deprecated since 2.3.3 and will be required in 3.0',
                \E_USER_DEPRECATED
            );

            $jwtClaim = 'https://eonx.com/user';
        }

        $this->jwtClaim = $jwtClaim;
        $this->jwtClaimFetcher = $jwtClaimFetcher ?? new JwtClaimFetcher();

        parent::__construct($priority);
    }

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    protected function getClaim(JwtEasyApiTokenInterface $token, string $claim, $default = null)
    {
        return $this->jwtClaimFetcher->getClaim($token, $claim, $default);
    }

    /**
     * @param null|mixed[] $default
     *
     * @return mixed[]
     */
    protected function getMainClaim(JwtEasyApiTokenInterface $token, ?array $default = null): array
    {
        return $this->jwtClaimFetcher->getArrayClaim($token, $this->jwtClaim, $default);
    }
}
