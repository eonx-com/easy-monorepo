<?php
declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Filesystem\Filesystem;

final class FilesystemFactoryStub
{
    public function create(): Filesystem
    {
        return new Filesystem();
    }
}
