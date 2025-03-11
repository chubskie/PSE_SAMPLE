<?php

include "db.php";
include "functions.php";

if (isset($_POST['delete'])) {
    $id = sanitizeInput($_POST['id']);

    $statement = $connection->prepare("DELETE FROM residents WHERE resident_code = ?");

    if ($statement) {
        $statement->bind_param("s", $id);

        if ($statement->execute()) {
            $statement->close();
            header("Location: index.php");
            exit;
        } else {
            error_log("Delete error: " . $statement->error);
            echo "Error: Failed to delete resident.";
            $statement->close();
        }
    } else {
        error_log("Prepare error: " . $connection->error);
        error_log("Database error: " . $connection->error);
    }
}