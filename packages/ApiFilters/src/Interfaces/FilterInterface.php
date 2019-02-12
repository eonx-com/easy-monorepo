<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiFilters\Interfaces;

interface FilterInterface
{
    /**
     * Get filter name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get filter slug.
     *
     * @return string
     */
    public function getSlug(): string;

    /**
     * Get the filter description.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get the filter options.
     *
     * @return \StepTheFkUp\ApiFilters\Interfaces\OptionInterface[]
     */
    public function getOptions(): array;
}
