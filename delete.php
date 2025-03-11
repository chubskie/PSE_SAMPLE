<?php

include "db.php";
include "functions.php";

if (isset($_POST['delete'])) {
    $id = sanitizeInput($_POST['id']);
    $query = "DELETE FROM residents WHERE resident_code = '$id';";

    if ($connection->query($query)) {
        $connection->close();
        header("Location: index.php");
    } else {
        echo "Error: " . $connection->error;
    }
}