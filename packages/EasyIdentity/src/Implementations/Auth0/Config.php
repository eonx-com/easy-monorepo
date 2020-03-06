<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations\Auth0;

use EonX\EasyIdentity\Exceptions\RequiredDataMissingException;

class Config
{
    /**
     * @var mixed[]
     */
    private $data;

    /**
     * @param null|mixed[] $data
     */
    public function __construct(?array $data = null)
    {
        $this->data = $data ?? [];
    }

    public function getClientId(): string
    {
        return (string)$this->getRequiredData('client_id');
    }

    public function getClientSecret(): string
    {
        return (string)$this->getRequiredData('client_secret');
    }

    public function getConnection(): string
    {
        return (string)$this->getRequiredData('connection');
    }

    public function getDomain(): string
    {
        return (string)$this->getRequiredData('domain');
    }

    /**
     * @return mixed
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    private function getRequiredData(string $key)
    {
        $value = $this->data[$key] ?? null;

        if ($value !== null) {
            return $value;
        }

        throw new RequiredDataMissingException(\sprintf('Required identity data for "%s" missing', $key));
    }
}
