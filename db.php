<?php
$connection = new mysqli('localhost', 'root', '', 'pse_sample');

if (!$connection)
{
    echo "Connection has failed.";
    die(mysqli_error($connection));
}