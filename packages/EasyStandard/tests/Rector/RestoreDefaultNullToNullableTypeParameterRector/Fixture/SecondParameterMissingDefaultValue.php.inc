<?php

class SecondParameterMissingDefaultValue
{
    private function someMethod(string $param, ?string $value): void
    {
        echo $param, $value;
    }
}

?>

-----
<?php

class SecondParameterMissingDefaultValue
{
    private function someMethod(string $param, ?string $value): void
    {
        echo $param, $value;
    }
}

?>
