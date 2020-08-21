<?php

class SecondParameterMissingDefaultValue
{
    public function __construct(string $param, ?string $value)
    {
        echo $param, $value;
    }
}

?>

-----
<?php

class SecondParameterMissingDefaultValue
{
    public function __construct(string $param, ?string $value = null)
    {
        echo $param, $value;
    }
}

?>
