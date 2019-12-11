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
     * Config constructor.
     *
     * @param null|mixed[] $data
     */
    public function __construct(?array $data = null)
    {
        $this->data = $data ?? [];
    }

    /**
     * Get client ID.
     *
     * @return string
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function getClientId(): string
    {
        return (string)$this->getRequiredData('client_id');
    }

    /**
     * Get client secret.
     *
     * @return string
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function getClientSecret(): string
    {
        return (string)$this->getRequiredData('client_secret');
    }

    /**
     * Get connection.
     *
     * @return string
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function getConnection(): string
    {
        return (string)$this->getRequiredData('connection');
    }

    /**
     * Get domain.
     *
     * @return string
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function getDomain(): string
    {
        return (string)$this->getRequiredData('domain');
    }

    /**
     * Get required data for given key.
     *
     * @param string $key
     *
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
