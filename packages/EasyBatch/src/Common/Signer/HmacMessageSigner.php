<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Signer;

use InvalidArgumentException;
use SensitiveParameter;

final readonly class HmacMessageSigner implements MessageSignerInterface
{
    private const string ALGORITHM = 'sha256';

    private const string KEY_DOMAIN = 'easy-batch:message:v1';

    /**
     * @var non-empty-list<string>
     */
    private array $keys;

    /**
     * @param string|string[] $secrets The first one signs, all of them verify
     */
    public function __construct(
        #[SensitiveParameter]
        string|array $secrets,
    ) {
        $keys = [];

        foreach ((array)$secrets as $secret) {
            if ($secret === '') {
                throw new InvalidArgumentException('Signing secret must not be empty.');
            }

            $keys[] = \hash_hmac(self::ALGORITHM, self::KEY_DOMAIN, $secret);
        }

        if ($keys === []) {
            throw new InvalidArgumentException('At least one signing secret must be provided.');
        }

        $this->keys = $keys;
    }

    public function sign(string $payload): string
    {
        return \hash_hmac(self::ALGORITHM, $payload, $this->keys[0]);
    }

    public function verify(string $payload, string $signature): bool
    {
        return \array_any(
            $this->keys,
            static fn(string $key): bool => \hash_equals(\hash_hmac(self::ALGORITHM, $payload, $key), $signature)
        );
    }
}
