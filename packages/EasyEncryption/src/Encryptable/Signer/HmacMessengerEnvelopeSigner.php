<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Signer;

use InvalidArgumentException;

final readonly class HmacMessengerEnvelopeSigner implements MessengerEnvelopeSignerInterface
{
    /**
     * @var non-empty-list<string>
     */
    private array $signingKeys;

    /**
     * @param string|string[] $signingKeys One or more keys. The first signs; every key verifies, so an old key
     *        kept last keeps accepting messages queued before a rotation until they drain.
     */
    public function __construct(
        string|array $signingKeys,
        private string $algorithm = 'sha512',
    ) {
        $keys = \array_values(\array_filter((array)$signingKeys, static fn(string $key): bool => $key !== ''));

        if ($keys === []) {
            throw new InvalidArgumentException('At least one non-empty signing key must be provided.');
        }

        $this->signingKeys = $keys;
    }

    public function sign(string $payload): string
    {
        return \hash_hmac($this->algorithm, $payload, $this->signingKeys[0]);
    }

    public function verify(string $payload, string $signature): bool
    {
        foreach ($this->signingKeys as $key) {
            if (\hash_equals(\hash_hmac($this->algorithm, $payload, $key), $signature)) {
                return true;
            }
        }

        return false;
    }
}
