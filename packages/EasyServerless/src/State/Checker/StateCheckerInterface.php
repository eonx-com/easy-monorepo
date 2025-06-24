<?php
declare(strict_types=1);

namespace EonX\EasyServerless\State\Checker;

interface StateCheckerInterface
{
    /**
     * Check the state of the application, if the state is not valid,
     * an exception should be thrown to indicate the issue.
     */
    public function check(): void;
}
