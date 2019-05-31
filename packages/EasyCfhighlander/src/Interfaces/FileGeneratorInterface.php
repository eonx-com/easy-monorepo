<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Interfaces;

use LoyaltyCorp\EasyCfhighlander\File\FileStatus;
use LoyaltyCorp\EasyCfhighlander\File\FileToGenerate;

interface FileGeneratorInterface
{
    /** @var string[] */
    public const STATUSES = [
        self::STATUS_CREATED,
        self::STATUS_SKIPPED_IDENTICAL,
        self::STATUS_UPDATED
    ];

    /** @var string[] */
    public const STATUSES_TO_TRIGGER_MANIFEST = [
        self::STATUS_CREATED,
        self::STATUS_UPDATED
    ];

    /** @var string */
    public const STATUS_CREATED = 'created';

    /** @var string */
    public const STATUS_SKIPPED_IDENTICAL = 'skipped_identical';

    /** @var string */
    public const STATUS_UPDATED = 'updated';

    /**
     * Generate file for given template and params.
     *
     * @param \LoyaltyCorp\EasyCfhighlander\File\FileToGenerate $fileToGenerate
     * @param null|mixed[] $params
     *
     * @return \LoyaltyCorp\EasyCfhighlander\File\FileStatus
     */
    public function generate(FileToGenerate $fileToGenerate, ?array $params = null): FileStatus;
}
