<?php
if (!\in_array('value', [])) {
    echo 'not in array';
}
?>

-----
<?php
if (\in_array('value', []) === false) {
    echo 'not in array';
}
?>
