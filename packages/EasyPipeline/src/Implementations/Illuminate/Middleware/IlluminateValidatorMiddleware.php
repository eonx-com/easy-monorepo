<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Implementations\Illuminate\Middleware;

use StepTheFkUp\EasyPipeline\Interfaces\MiddlewareInterface;

final class IlluminateValidatorMiddleware implements MiddlewareInterface
{
    private $validator;

    private $rules;

    /**
     * IlluminateValidatorMiddleware constructor.
     *
     * @param $validator
     * @param $rules
     */
    public function __construct($validator, array $rules)
    {
        $this->validator = $validator;
        $this->rules = $rules;
    }


    /**
     * Handle given input and pass return through next.
     *
     * @param mixed $input
     * @param callable $next
     *
     * @return mixed
     */
    public function handle($input, callable $next)
    {
        $this->validator->make($rules, $input)->validate();

        return $next($input);
    }
}
