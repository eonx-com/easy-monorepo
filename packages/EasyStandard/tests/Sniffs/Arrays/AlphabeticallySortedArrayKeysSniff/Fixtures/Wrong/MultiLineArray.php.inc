<?php

$array = [
    'z' => 0,
    'a' => 1,
    'b' => 3,
];

$array2 = array(
    'z' => 0,
    'a' => 1,
    'b' => 3,
);
?>
-----
<?php

$array = [
    'a' => 1,
    'b' => 3,
    'z' => 0,
];

$array2 = array(
    'a' => 1,
    'b' => 3,
    'z' => 0,
);
?>
