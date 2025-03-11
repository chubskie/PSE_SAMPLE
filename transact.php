<?php

include "db.php";
include "functions.php";

$errors = [];

$resident_code = null;
$year = null;
$grant = null;

$query1 = "SELECT * FROM residents";
$resident_list = mysqli_query($connection, $query1);

if (isset($_POST['transact'])) {
    // Sanitize input
    $resident_code = sanitizeInput($_POST['resident_code']);
    $year = sanitizeInput($_POST['year']);

    // Validate Input
    if (!is_numeric($year)) {
        $errors['year'] = "Please enter a valid year.";
    } else if ($year <= 1900 || $year >= 2999) {
        $errors['year'] = "Please enter a year from 1900 to 2099.";
    }

    // Validation using Prepared Statements
    $query2 = "SELECT COUNT(*) FROM residents WHERE resident_code = ?"; // Check if resident code exists
    $stmt = $connection->prepare($query2);

    if ($stmt) {
        $stmt->bind_param("s", $resident_code);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    
        if ($count == 0) {
            $errors['resident_code'] = "Resident code does not exist.";
        }
    } else {
        // Handle database preparation error
        $errors['resident_code'] = "Database error.";
        error_log("Database error: " . $connection->error);
    }


    if (count($errors) == 0) {
        $statement = $connection->prepare("SELECT * FROM residents WHERE resident_code = ?");

        if ($statement) {
            $statement->bind_param("s", $resident_code);
            $statement->execute();

            $result = $statement->get_result();

            if ($result && $result->num_rows == 1) {
                $info = $result->fetch_assoc();

                if ($info) {
                    // Check monthly salary
                    if ($info['monthly_salary'] <= 14000) {
                        $birth_year = intval(date_format(date_create($info['birth_date']), "Y"));
                        if ($birth_year > $year) {
                            $errors['transaction'] = "Resident not yet alive during year of registration.";
                        } else {
                            $age = $year - $birth_year;
                            $counter = $age;

                            $grant = 0;

                            while ($counter <= 17) {
                                if ($counter >= 4 && $counter <= 11) {
                                    $grant += (300 * 12);
                                } else if ($counter >= 12 && $counter <= 15) {
                                    $grant += (500 * 12);
                                } else if ($counter >= 16 && $counter <= 17) {
                                    $grant += (700 * 12);
                                }
                                
                                $counter++;
                            }
                        }
                    } else {
                        $errors['transaction'] = "Resident is not eligible for cash grant due to their family's average monthly salary.";
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Transaction Module</h1>
    <form action="transact.php" method="POST">
        <div class="form-control">
            <label for="resident_code">Resident Code</label>
            <select name="resident_code" id="resident_code">
                <?php
                if ($resident_list) {
                    while ($row = mysqli_fetch_assoc($resident_list)) {
                        ?>
                        <option value="<?= $row['resident_code'] ?>" <?= isset($_POST['transact']) && $_POST['resident_code'] == $row['resident_code'] ? 'selected' : '' ?> >
                            <?= $row['resident_code'] ?></option>
                        <?php
                    }
                }
                ?>
            </select>
            <?php
            if (isset($_POST['transact']) && isset($errors['resident_code'])) {
                ?>
                <p class="error_msg"><?= $errors['resident_code'] ?></p>
                <?php
            }
            ?>
        </div>
        <div class="form-control">
            <label for="year">Year of Application</label>
            <input type="number" name="year" id="year" min="1900" max="2099" value="<?= $year ?? null ?>" />
            <?php
            if (isset($_POST['transact']) && isset($errors['year'])) {
                ?>
                <p class="error_msg"><?= $errors['year'] ?></p>
                <?php
            }
            ?>
        </div>
        <div>
            <a href="index.php">Cancel</a>
            <button type="submit" name="transact">Calculate Cash Grant</button>
        </div>
    </form>
    <br>

    <?php
    if (isset($_POST['transact']) && count($errors) == 0) {
        ?>
        <p>Resident Code: <?= $resident_code ?></p>
        <p>Year of Application: <?= $year ?></p>
        <?php
    }
    if ($grant > 0) {
        ?>
        <p>Total Cash Grant: Php <?= number_format($grant, 2) ?></p>
        <?php
    } else if (isset($errors['transaction'])) {
        ?>
        <p class="error_msg"><?= $errors['transaction'] ?></p>
        <?php
    }
    ?>
</body>
</html>