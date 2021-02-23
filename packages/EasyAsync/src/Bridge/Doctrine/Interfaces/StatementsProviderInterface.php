<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Doctrine\Interfaces;

interface StatementsProviderInterface
{
    /**
     * @return iterable<string>
     */
    public static function migrateStatements(): iterable;

    /**
     * @return iterable<string>
     */
    public static function rollbackStatements(): iterable;
}
