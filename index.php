<?php
include "db.php";
include "functions.php";

$query = null;

if (isset($_GET['search']) && strlen($_GET['search']) > 0) {
    $search = sanitizeInput($_GET['search']);

    $query = "SELECT * FROM residents
                WHERE resident_code LIKE '%{$search}%'
                OR family_name LIKE '%{$search}%'
                OR given_name LIKE '%{$search}%'
                OR middle_name LIKE '%{$search}%'";
} else {
    $query = "SELECT * FROM residents";
}

$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIEW</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div>
        <h1>Resident List</h1>
        <div>
            <form action="index.php" method="get" class="search-form">
                <input type="text" name="search" id="search" value="<?= $search ?? null ?>" placeholder="Search for resident code or name...">
                <button type="submit">Search</button>
            </form>
            <a href="add.php" id="add-btn">Add Resident</a>
            <a href="transact.php" id="transact-btn">Transaction</a>
        </div>
        <div>
            <table>
                <tr>
                    <th>Resident Code</th>
                    <th>Family Name</th>
                    <th>Given Name</th>
                    <th>Middle Name</th>
                    <th>Birth Date</th>
                    <th>Family Avg Monthly Salary</th>
                    <th>Actions</th>
                </tr>
                <!-- Show table rows based on query result -->
                <?php
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>
                        <tr>
                            <td><?= $row['resident_code'] ?></td>
                            <td><?= $row['family_name'] ?></td>
                            <td><?= $row['given_name'] ?></td>
                            <td><?= $row['middle_name'] == null ? '-' : $row['middle_name'] ?></td>
                            <td><?= date_format(date_create($row['birth_date']), "M j, Y") ?></td>
                            <td><?= "Php " . number_format($row['monthly_salary'], 2) ?></td>
                            <td class="table-actions">
                                <a href="edit.php?id=<?= $row['resident_code'] ?>">Edit</a>
                                <form action="delete.php" method="POST">
                                    <input type="hidden" name="id" value="<?= $row['resident_code'] ?>">
                                    <button type="submit" name="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                <?php
                    }
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>