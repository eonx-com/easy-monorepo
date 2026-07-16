<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Constraint;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class Acn extends AbstractConstraint
{
    public const string COMPLEMENT_CALCULATION_FAILED_ERROR = 'COMPLEMENT_CALCULATION_FAILED_ERROR';

    public const string INVALID_CHARACTERS_ERROR = 'INVALID_CHARACTERS_ERROR';

    public const string INVALID_LENGTH_ERROR = 'INVALID_LENGTH_ERROR';

    public string $message = 'acn.not_valid';

    public function __construct(?array $groups = null, ?string $message = null)
    {
        $this->message = $message ?? $this->message;

        parent::__construct(groups: $groups);
    }
}
