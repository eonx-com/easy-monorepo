<?php

class MissingDefaultValue
{
    public function __construct(?string $value)
    {
        echo $value;
    }
}

?>

-----
<?php

class MissingDefaultValue
{
    public function __construct(?string $value = null)
    {
        echo $value;
    }
}

?>
