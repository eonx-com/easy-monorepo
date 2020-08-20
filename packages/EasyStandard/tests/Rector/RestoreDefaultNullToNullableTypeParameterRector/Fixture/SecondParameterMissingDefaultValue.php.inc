<?php

class SecondParameterMissingDefaultValue
{
    public function __construct(string $param, ?string $value)
    {
        // no code needed
    }
}

?>

-----
<?php

class SecondParameterMissingDefaultValue
{
    public function __construct(string $param, ?string $value = null)
    {
        // no code needed
    }
}

?>
