<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Tests\Stubs;

use stdClass;

class ClassStub
{
    /**
     * A test method that returns a test object.
     *
     * @param string $param1
     * @param int $param2
     *
     * @return null|\stdClass
     */
    public function methodOne(string $param1, int $param2): ?stdClass
    {
        return $this->cantMockThis($param1, $param2);
    }

    /**
     * A test method that accepts object.
     *
     * @param $object
     *
     * @return bool
     */
    public function methodThree($object): bool
    {
        return $object instanceof stdClass;
    }

    /**
     * A test method that returns self.
     *
     * @param string $param1
     * @param int $param2
     *
     * @return \StepTheFkUp\MockBuilder\Tests\Stubs\ClassStub
     */
    public function methodTwo(string $param1, ?int $param2 = null): self
    {
        $this->cantMockThis($param1, $param2 ?? 1234567890);

        return $this;
    }

    /**
     * A test method that cannot and should not be mocked since it's private.
     *
     * @param string $param1
     * @param int $param2
     *
     * @return \stdClass
     */
    private function cantMockThis(string $param1, int $param2): stdClass
    {
        $object = new stdClass();

        $object->param1 = $param1;
        $object->param2 = $param2;

        return $object;
    }
}
