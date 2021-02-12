<?php
declare(strict_types=1);

$someClass = new \stdClass();

// my single comment.
$someClass->test = 'test';

?>

-----
<?php
declare(strict_types=1);

$someClass = new \stdClass();

// My single comment
$someClass->test = 'test';

?>
