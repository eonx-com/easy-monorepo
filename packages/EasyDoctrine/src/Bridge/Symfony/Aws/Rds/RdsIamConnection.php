<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds;

use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Connection;

final class RdsIamConnection extends Connection
{
    public function connect(): bool
    {
        if ($this->_conn !== null) {
            return false;
        }

        $params = $this->getParams();
        $params['password'] = \call_user_func($params['passwordGenerator'], $params);

        // DBAL v2
        if (\method_exists($this, 'convertException') === false) {
            $driverOptions = $params['driverOptions'] ?? [];
            $user = $params['user'] ?? null;
            $password = $params['password'] ?? null;

            $this->_conn = $this->_driver->connect($params, $user, $password, $driverOptions);
        }

        // DBAL v3
        if (\method_exists($this, 'convertException')) {
            try {
                $this->_conn = $this->_driver->connect($params);
            } catch (DriverException $driverException) {
                throw $this->convertException($driverException);
            }
        }

        return parent::connect();
    }
}
