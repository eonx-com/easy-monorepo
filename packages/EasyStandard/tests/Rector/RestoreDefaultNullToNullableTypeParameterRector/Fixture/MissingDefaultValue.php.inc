<?php

class MissingDefaultValue
{
    public function __construct(?string $value)
    {
        // no code needed
    }
}

?>

-----
<?php

class MissingDefaultValue
{
    public function __construct(?string $value = null)
    {
        // no code needed
    }
}

?>
