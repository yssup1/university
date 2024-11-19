<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Management</title>
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

// Create or Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomNumber = $_POST['room_number'];
    $building = $_POST['building'];
    $capacity = $_POST['capacity'];
    $classroomId = $_POST['classroom_id'] ?? null;

    // Use prepared statements to prevent SQL injection
    if ($classroomId) {
        // Update existing classroom
        $stmt = $conn->prepare("UPDATE Classroom SET room_number = ?, building = ?, capacity = ? WHERE classroom_id = ?");
        $stmt->bind_param("ssii", $roomNumber, $building, $capacity, $classroomId);
    } else {
        // Insert new classroom
        $stmt = $conn->prepare("INSERT INTO Classroom (room_number, building, capacity) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $roomNumber, $building, $capacity);
    }
    
    if ($stmt->execute()) {
        echo "Classroom saved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Read classrooms
$result = $conn->query("SELECT * FROM Classroom");

// Display classrooms without the ID column
echo "<h2>Classrooms</h2>";
echo "<table border='1'>";
echo "<tr><th>Room Number</th><th>Building</th><th>Capacity</th><th>Actions</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['room_number'] . "</td>";
    echo "<td>" . $row['building'] . "</td>";
    echo "<td>" . $row['capacity'] . "</td>";
    echo "<td>
            <a href='?edit=" . $row['classroom_id'] . "'>Edit</a> | 
            <a href='?delete=" . $row['classroom_id'] . "'>Delete</a>
          </td>";
    echo "</tr>";
}
echo "</table>";

// Handle delete
if (isset($_GET['delete'])) {
    $classroomId = $_GET['delete'];
    $deleteStmt = $conn->prepare("DELETE FROM Classroom WHERE classroom_id = ?");
    $deleteStmt->bind_param("i", $classroomId);
    if ($deleteStmt->execute()) {
        echo "Classroom deleted successfully.";
        header("Location: classroom.php"); // Redirect to avoid re-submission
        exit;
    } else {
        echo "Error: " . $deleteStmt->error;
    }
    $deleteStmt->close();
}

// Edit classroom
$classroomToEdit = null;
if (isset($_GET['edit'])) {
    $classroomId = $_GET['edit'];
    $editStmt = $conn->prepare("SELECT * FROM Classroom WHERE classroom_id = ?");
    $editStmt->bind_param("i", $classroomId);
    $editStmt->execute();
    $classroomToEdit = $editStmt->get_result()->fetch_assoc();
    $editStmt->close();
}
?>

<!-- Form for adding or editing a classroom -->
<form method="POST">
    <input type="hidden" name="classroom_id" value="<?php echo $classroomToEdit['classroom_id'] ?? ''; ?>">
    <input type="text" name="room_number" placeholder="Room Number" value="<?php echo $classroomToEdit['room_number'] ?? ''; ?>" required>
    <input type="text" name="building" placeholder="Building" value="<?php echo $classroomToEdit['building'] ?? ''; ?>" required>
    <input type="number" name="capacity" placeholder="Capacity" value="<?php echo $classroomToEdit['capacity'] ?? ''; ?>" required>
    <button type="submit"><?php echo isset($classroomToEdit) ? 'Update Classroom' : 'Add Classroom'; ?></button>
</form>

<!-- Back button -->
<button onclick="window.location.href='index.php';">Back</button>

<?php
// Close the connection
$conn->close();
?>

</body>
</html>
