<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Management</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->
</head>
<body>

<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'university');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create or Edit Instructor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['first_name'];
    $middleInitial = $_POST['middle_initial'];
    $lastName = $_POST['last_name'];
    $streetNumber = $_POST['street_number'];
    $streetName = $_POST['street_name'];
    $aptNumber = $_POST['apt_number'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postalCode = $_POST['postal_code'];
    $dateOfBirth = $_POST['date_of_birth'];
    $departmentId = $_POST['department_id'];
    $salary = $_POST['salary'];  // New field for salary
    
    // Check if an instructor ID is provided for editing
    if (isset($_POST['instructor_id'])) {
        $instructorId = $_POST['instructor_id'];

        // Update the instructor
        $stmt = $conn->prepare("UPDATE Instructor SET first_name=?, middle_initial=?, last_name=?, street_number=?, street_name=?, apt_number=?, city=?, state=?, postal_code=?, date_of_birth=?, department_id=?, salary=? WHERE instructor_id=?");
        $stmt->bind_param("ssssssssssiii", $firstName, $middleInitial, $lastName, $streetNumber, $streetName, $aptNumber, $city, $state, $postalCode, $dateOfBirth, $departmentId, $salary, $instructorId);

        if ($stmt->execute()) {
            echo "Instructor updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insert new instructor
        $stmt = $conn->prepare("INSERT INTO Instructor (first_name, middle_initial, last_name, street_number, street_name, apt_number, city, state, postal_code, date_of_birth, department_id, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssii", $firstName, $middleInitial, $lastName, $streetNumber, $streetName, $aptNumber, $city, $state, $postalCode, $dateOfBirth, $departmentId, $salary);

        if ($stmt->execute()) {
            echo "Instructor added successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Delete instructor
if (isset($_GET['delete_id'])) {
    $instructorId = $_GET['delete_id'];

    // Delete the instructor from the database
    $stmt = $conn->prepare("DELETE FROM Instructor WHERE instructor_id=?");
    $stmt->bind_param("i", $instructorId);

    if ($stmt->execute()) {
        echo "Instructor deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Read instructors
$result = $conn->query("SELECT * FROM Instructor");

?>

<!-- Display instructors -->
<h2>Instructors</h2>
<table border='1'>
    <tr>
        <th>First Name</th>
        <th>Middle Initial</th>
        <th>Last Name</th>
        <th>Address</th>
        <th>City</th>
        <th>State</th>
        <th>Postal Code</th>
        <th>Date of Birth</th>
        <th>Department</th>
        <th>Salary</th>
        <th>Actions</th>
    </tr>

<?php
while ($row = $result->fetch_assoc()) {
    // Fetch department name for this instructor
    $deptResult = $conn->query("SELECT department_name FROM Department WHERE department_id = " . $row['department_id']);
    $deptRow = $deptResult->fetch_assoc();

    echo "<tr>";
    echo "<td>" . $row['first_name'] . "</td>";
    echo "<td>" . $row['middle_initial'] . "</td>";
    echo "<td>" . $row['last_name'] . "</td>";
    echo "<td>" . $row['street_number'] . " " . $row['street_name'] . " " . $row['apt_number'] . "</td>";
    echo "<td>" . $row['city'] . "</td>";
    echo "<td>" . $row['state'] . "</td>";
    echo "<td>" . $row['postal_code'] . "</td>";
    echo "<td>" . $row['date_of_birth'] . "</td>";
    echo "<td>" . $deptRow['department_name'] . "</td>";
    echo "<td>" . number_format($row['salary'], 2) . "</td>"; // Display the salary
    echo "<td>
        <a href='?edit_id=" . $row['instructor_id'] . "'>Edit</a> | 
        <a href='?delete_id=" . $row['instructor_id'] . "' onclick='return confirm(\"Are you sure you want to delete this instructor?\")'>Delete</a>
    </td>";
    echo "</tr>";
}
echo "</table>";

?>

<!-- Form for adding/editing an instructor -->
<?php
// Check if we are editing an existing instructor
if (isset($_GET['edit_id'])) {
    $instructorId = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM Instructor WHERE instructor_id = $instructorId");
    $row = $result->fetch_assoc();
}
?>

<h2><?php echo isset($row) ? "Edit Instructor" : "Add New Instructor"; ?></h2>
<form method="POST">
    <?php if (isset($row)): ?>
        <input type="hidden" name="instructor_id" value="<?php echo $row['instructor_id']; ?>">
    <?php endif; ?>
    <input type="text" name="first_name" placeholder="First Name" required value="<?php echo isset($row) ? $row['first_name'] : ''; ?>">
    <input type="text" name="middle_initial" placeholder="Middle Initial" value="<?php echo isset($row) ? $row['middle_initial'] : ''; ?>">
    <input type="text" name="last_name" placeholder="Last Name" required value="<?php echo isset($row) ? $row['last_name'] : ''; ?>">
    <input type="text" name="street_number" placeholder="Street Number" value="<?php echo isset($row) ? $row['street_number'] : ''; ?>">
    <input type="text" name="street_name" placeholder="Street Name" value="<?php echo isset($row) ? $row['street_name'] : ''; ?>">
    <input type="text" name="apt_number" placeholder="Apt Number" value="<?php echo isset($row) ? $row['apt_number'] : ''; ?>">
    <input type="text" name="city" placeholder="City" value="<?php echo isset($row) ? $row['city'] : ''; ?>">
    <input type="text" name="state" placeholder="State" value="<?php echo isset($row) ? $row['state'] : ''; ?>">
    <input type="text" name="postal_code" placeholder="Postal Code" value="<?php echo isset($row) ? $row['postal_code'] : ''; ?>">
    <input type="date" name="date_of_birth" required value="<?php echo isset($row) ? $row['date_of_birth'] : ''; ?>">
    <select name="department_id" required>
        <option value="">Select Department</option>
        <?php
        $departments = $conn->query("SELECT department_id, department_name FROM Department");
        while ($dept = $departments->fetch_assoc()) {
            echo "<option value='{$dept['department_id']}' " . (isset($row) && $row['department_id'] == $dept['department_id'] ? 'selected' : '') . ">{$dept['department_name']}</option>";
        }
        ?>
    </select>
    <input type="number" name="salary" placeholder="Salary" required value="<?php echo isset($row) ? $row['salary'] : ''; ?>">
    <button type="submit"><?php echo isset($row) ? "Update Instructor" : "Add Instructor"; ?></button>
</form>

<!-- Back button -->
<button onclick="window.location.href='index.php';">Back</button>

<?php
// Close the connection
$conn->close();
?>

</body>
</html>
