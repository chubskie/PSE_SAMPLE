<?php
include "db.php";
include "functions.php";

$errors = [];

// Get resident code from url parameters
$family_name = null;
$given_name = null;
$middle_name = null;
$birth_date = null;
$monthly_salary = null;

if (!isset($_POST['edit'])) {
    $resident_code = sanitizeInput($_GET['id']);
    $query = "SELECT * FROM residents WHERE resident_code = '$resident_code';";

    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        $family_name = $row['family_name'];
        $given_name = $row['given_name'];
        $middle_name = $row['middle_name'];
        $birth_date = new DateTime($row['birth_date']);
        $monthly_salary = doubleval($row['monthly_salary']);
    } else {
        echo "ID not found.";
        die();
    }
} else {
    $resident_code = sanitizeInput($_POST['resident_code']);
    $family_name = sanitizeInput($_POST['family_name']);
    $given_name = sanitizeInput($_POST['given_name']);
    $middle_name = sanitizeInput($_POST['middle_name']);
    $birth_date = sanitizeInput($_POST['birth_date']);
    $monthly_salary = sanitizeInput($_POST['monthly_salary']);
    
    //-------- Validate inputs

    // Text Inputs
    if (empty($family_name)) {
        $errors['family_name'] = "Please enter a family name.";
    } else if (strlen($family_name) > 40) {
        $errors['family_name'] = "Family name cannot exceed 40 characters.";
    }

    if (empty($given_name)) {
        $errors['given_name'] = "Please enter a given name.";
    } else if (strlen($given_name) > 40) {
        $errors['given_name'] = "Given name cannot exceed 40 characters.";
    }

    // Article 176 of the Family Code allows having no middle names.
    if (strlen($middle_name) > 40) {
        $errors['middle_name'] = "Middle name cannot exceed 40 characters.";
    }

    // Date inputs
    if ($birth_date == null) {
        $errors['birth_date'] = "Please enter a valid birth date.";
    } else {
        $birth_date = new DateTime($birth_date);
        $now = new DateTime();

        if ($birth_date > $now) {
            $errors['birth_date'] = "Birth date cannot be a future date.";
        }
    }

    // Number inputs
    if ($monthly_salary == null) {
        $errors['monthly_salary'] = "Please enter the family's average monthly salary.";
    } else if (!is_numeric($monthly_salary) && doubleval($monthly_salary) < 0) {
        $errors['monthly_salary'] = "Monthly salary must be a positive numeric value.";
    }

    if (count($errors) == 0) {
        $birth_date = strval($birth_date->format("Y-m-d"));

        $query = "UPDATE residents
                    SET family_name = '$family_name',
                        given_name = '$given_name',
                        middle_name = '$middle_name',
                        birth_date = '$birth_date',
                        monthly_salary = '$monthly_salary' WHERE resident_code = '$resident_code'";

        if ($connection->query($query)) {
            $connection->close();
            header("Location: index.php");
        } else {
            echo "Error: " . $connection->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDIT RESIDENT</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="index.php">Go Back</a>
    <h1>Edit Resident</h1>
    <form action="edit.php" method="POST">
        <input type="hidden" name="resident_code" value="<?= $resident_code ?>">
        <div class="form-control">
            <label for="family_name">Family Name</label>
            <input type="text" name="family_name" id="family_name" value="<?= $family_name ?>">
            <?php
                if (isset($_POST['edit']) && isset($errors['family_name'])) {
                    echo "<p class='error_msg'>" . $errors['family_name'] . "</p>";
                }
            ?>
        </div>
        <div class="form-control">
            <label for="given_name">Given Name</label>
            <input type="text" name="given_name" id="given_name" value="<?= $given_name ?>">
            <?php
                if (isset($_POST['edit']) && isset($errors['given_name'])) {
                    echo "<p class='error_msg'>" . $errors['given_name'] . "</p>";
                }
            ?>
        </div>
        <div class="form-control">
            <label for="middle_name">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name" value="<?= $middle_name ?>">
            <?php
                if (isset($_POST['edit']) && isset($errors['middle_name'])) {
                    echo "<p class='error_msg'>" . $errors['middle_name'] . "</p>";
                }
            ?>
        </div>
        <div class="form-control">
            <label for="birth_date">Birth Date</label>
            <input type="date" name="birth_date" id="birth_date" value='<?= $birth_date ? $birth_date->format("Y-m-d") : null ?>'>
            <?php
                if (isset($_POST['edit']) && isset($errors['birth_date'])) {
                    echo "<p class='error_msg'>" . $errors['birth_date'] . "</p>";
                }
            ?>
        </div>
        <div class="form-control">
            <label for="monthly_salary">Average Monthly Family Salary</label>
            <input type="number" name="monthly_salary" id="monthly_salary" step="0.01" min="0" value='<?= $monthly_salary ?>'>
            <?php
                if (isset($_POST['edit']) && isset($errors['monthly_salary'])) {
                    echo "<p class='error_msg'>" . $errors['monthly_salary'] . "</p>";
                }
            ?>
        </div>
        <div>
            <a href="index.php">Cancel</a>
            <button type="submit" name="edit">Save</button>
        </div>
    </form>
</body>
</html>