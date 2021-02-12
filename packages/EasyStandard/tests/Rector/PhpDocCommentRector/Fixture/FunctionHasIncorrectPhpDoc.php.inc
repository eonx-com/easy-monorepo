<?php
declare(strict_types=1);

/** my single comment */
function functionWithInCorrectPhpDoc(): string {
    return '';
}

?>

-----
<?php
declare(strict_types=1);

/** My single comment. */
function functionWithInCorrectPhpDoc(): string {
    return '';
}

?>
