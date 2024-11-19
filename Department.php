<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management</title>
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

// Initialize variable for department editing
$departmentToEdit = null;

// Handle edit request
if (isset($_GET['edit'])) {
    $departmentId = $_GET['edit'];
    $editStmt = $conn->prepare("SELECT department_id, department_name, budget, building FROM Department WHERE department_id = ?");
    $editStmt->bind_param("i", $departmentId);
    $editStmt->execute();
    $result = $editStmt->get_result();
    $departmentToEdit = $result->fetch_assoc();
    $editStmt->close();
}

// Create or Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $departmentName = $_POST['department_name'];
    $budget = $_POST['budget'];
    $building = $_POST['building'];
    $departmentId = $_POST['department_id'] ?? null;

    // Use prepared statements to prevent SQL injection
    if ($departmentId) {
        // Update existing department
        $stmt = $conn->prepare("UPDATE Department SET department_name = ?, budget = ?, building = ? WHERE department_id = ?");
        $stmt->bind_param("sisi", $departmentName, $budget, $building, $departmentId);
    } else {
        // Insert new department
        $stmt = $conn->prepare("INSERT INTO Department (department_name, budget, building) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $departmentName, $budget, $building);
    }
    
    if ($stmt->execute()) {
        echo "Department saved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Read departments
$result = $conn->query("SELECT department_id, department_name, budget, building FROM Department");

// Display departments without the ID column
echo "<h2>Departments</h2>";
echo "<table border='1'>";
echo "<tr><th>Name</th><th>Budget</th><th>Building</th><th>Actions</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['department_name'] . "</td>";
    echo "<td>" . $row['budget'] . "</td>";
    echo "<td>" . $row['building'] . "</td>";
    echo "<td>
            <a href='?edit=" . $row['department_id'] . "'>Edit</a> | 
            <a href='?delete=" . $row['department_id'] . "'>Delete</a>
          </td>";
    echo "</tr>";
}
echo "</table>";

// Delete department
if (isset($_GET['delete'])) {
    $departmentId = $_GET['delete'];
    $deleteStmt = $conn->prepare("DELETE FROM Department WHERE department_id = ?");
    $deleteStmt->bind_param("i", $departmentId);
    if ($deleteStmt->execute()) {
        echo "Department deleted successfully.";
        header("Location: department.php"); // Redirect to avoid re-submission
        exit;
    } else {
        echo "Error: " . $deleteStmt->error;
    }
    $deleteStmt->close();
}
?>

<!-- Form for adding or editing a department -->
<form method="POST">
    <input type="hidden" name="department_id" value="<?php echo $departmentToEdit['department_id'] ?? ''; ?>">
    <input type="text" name="department_name" placeholder="Department Name" value="<?php echo $departmentToEdit['department_name'] ?? ''; ?>" required>
    <input type="number" name="budget" placeholder="Budget" value="<?php echo $departmentToEdit['budget'] ?? ''; ?>" required>
    <input type="text" name="building" placeholder="Building" value="<?php echo $departmentToEdit['building'] ?? ''; ?>" required>
    <button type="submit"><?php echo $departmentToEdit ? 'Update Department' : 'Add Department'; ?></button>
</form>

<!-- Back button -->
<button onclick="window.location.href='index.php';">Back</button>

<?php
// Close the connection
$conn->close();
?>

</body>
</html>
