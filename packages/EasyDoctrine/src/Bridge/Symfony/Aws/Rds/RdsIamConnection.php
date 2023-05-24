<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds;

use Aws\Rds\AuthTokenGenerator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;

final class RdsIamConnection extends Connection
{
    private ?AuthTokenGenerator $authTokenGenerator = null;

    public function connect(): bool
    {
        if ($this->_conn !== null) {
            return false;
        }

        $params = $this->getParams();

        $this->authTokenGenerator ??= $params['authTokenGenerator'] ?? null;
        unset($params['authTokenGenerator']);

        $params['password'] = \call_user_func($params['passwordGenerator'], $this->authTokenGenerator, $params);

        // DBAL v2
        if (\method_exists(Connection::class, 'convertException') === false) {
            $driverOptions = $params['driverOptions'] ?? [];
            $user = $params['user'] ?? null;
            $password = $params['password'] ?? null;

            $this->_conn = $this->_driver->connect($params, $user, $password, $driverOptions);
        }

        // DBAL v3
        if (\method_exists(Connection::class, 'convertException')) {
            try {
                $this->_conn = $this->_driver->connect($params);
            } catch (DriverException $driverException) {
                throw $this->convertException($driverException);
            }
        }

        return parent::connect();
    }
}
