<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Interfaces;

interface PipelineInterface
{
    /**
     * Process set input through set middleware list and return processed input.
     *
     * @return mixed
     */
    public function process();

    /**
     * Set input to be processed.
     *
     * @param mixed $input
     *
     * @return \StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface
     */
    public function setInput($input): self;

    /**
     * Set middleware list to process input with.
     *
     * @param mixed[] $middlewareList
     *
     * @return \StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface
     */
    public function setMiddlewareList(array $middlewareList): self;

    /**
     * Return logs created by each middleware during process.
     *
     * @return mixed[]
     *
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\PipelineDidntRunException If called before process() is called
     */
    public function getLogs(): array;
}
