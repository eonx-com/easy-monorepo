<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use Carbon\Carbon;
use Throwable;

final class TimeBuilder extends AbstractErrorResponseBuilder
{
    /**
     * @var string
     */
    private $key;

    public function __construct(?string $key = null, ?int $priority = null)
    {
        $this->key = $key ?? 'time';

        parent::__construct($priority);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        $data[$this->key] = Carbon::now()->toIso8601ZuluString();

        return $data;
    }
}
