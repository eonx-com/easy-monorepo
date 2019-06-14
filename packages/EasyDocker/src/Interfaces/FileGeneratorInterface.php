<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDocker\Interfaces;

use LoyaltyCorp\EasyDocker\File\FileStatus;
use LoyaltyCorp\EasyDocker\File\File;

interface FileGeneratorInterface
{
    /** @var string[] */
    public const STATUSES = [
        self::STATUS_CREATED,
        self::STATUS_REMOVED,
        self::STATUS_SKIPPED_IDENTICAL,
        self::STATUS_SKIPPED_NO_FILE,
        self::STATUS_UPDATED
    ];

    /** @var string[] */
    public const STATUSES_TO_TRIGGER_MANIFEST = [
        self::STATUS_CREATED,
        self::STATUS_REMOVED,
        self::STATUS_UPDATED
    ];

    /** @var string */
    public const STATUS_CREATED = 'created';

    /** @var string */
    public const STATUS_REMOVED = 'removed';

    /** @var string */
    public const STATUS_SKIPPED_IDENTICAL = 'skipped_identical';

    /** @var string */
    public const STATUS_SKIPPED_NO_FILE = 'skipped_no_file';

    /** @var string */
    public const STATUS_UPDATED = 'updated';

    /**
     * Generate file for given template and params.
     *
     * @param \LoyaltyCorp\EasyDocker\File\File $fileToGenerate
     * @param null|mixed[] $params
     *
     * @return \LoyaltyCorp\EasyDocker\File\FileStatus
     */
    public function generate(File $fileToGenerate, ?array $params = null): FileStatus;

    /**
     * Remove given file.
     *
     * @param \LoyaltyCorp\EasyDocker\File\File $fileToRemove
     *
     * @return \LoyaltyCorp\EasyDocker\File\FileStatus
     */
    public function remove(File $fileToRemove): FileStatus;
}
