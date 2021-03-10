<?php
declare(strict_types=1);

//      my single comment.
function functionWithInCorrectSingleComment(): string {
    return '';
}

?>

-----
<?php
declare(strict_types=1);

// My single comment
function functionWithInCorrectSingleComment(): string {
    return '';
}

?>
