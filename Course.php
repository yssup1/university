<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
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

// Initialize the variable for editing
$courseToEdit = null;
$editStmt = null; // Initialize to avoid undefined variable error

// Handle edit request
if (isset($_GET['edit'])) {
    $courseId = $_GET['edit'];
    
    // Prepare the statement
    $editStmt = $conn->prepare("SELECT course_id, course_name, budget, building, total_credits FROM Course WHERE course_id = ?");
    if ($editStmt) {
        $editStmt->bind_param("i", $courseId);
        $editStmt->execute();
        $result = $editStmt->get_result();
        
        // Check if any course was found
        if ($result->num_rows > 0) {
            $courseToEdit = $result->fetch_assoc();
        } else {
            echo "No course found with ID: " . htmlspecialchars($courseId);
        }
        $editStmt->close(); // Close the statement after use
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Handle create or edit request (same as before)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $courseName = $_POST['course_name'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $building = $_POST['building'] ?? '';
    $totalCredits = $_POST['total_credits'] ?? '';
    $courseId = $_POST['course_id'] ?? null;

    // Use prepared statements to prevent SQL injection
    if ($courseId) {
        // Update existing course
        $stmt = $conn->prepare("UPDATE Course SET course_name = ?, budget = ?, building = ?, total_credits = ? WHERE course_id = ?");
        $stmt->bind_param("sssii", $courseName, $budget, $building, $totalCredits, $courseId);
    } else {
        // Insert new course
        $stmt = $conn->prepare("INSERT INTO Course (course_name, budget, building, total_credits) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $courseName, $budget, $building, $totalCredits);
    }

    if ($stmt->execute()) {
        echo "Course saved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Read courses
$result = $conn->query("SELECT course_id, course_name, budget, building, total_credits FROM Course");

// Display courses without the ID column
echo "<h2>Courses</h2>";
echo "<table border='1'>";
echo "<tr><th>Name</th><th>Budget</th><th>Building</th><th>Total Credits</th><th>Actions</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['course_name'] . "</td>";
    echo "<td>" . $row['budget'] . "</td>";
    echo "<td>" . $row['building'] . "</td>";
    echo "<td>" . $row['total_credits'] . "</td>";  // Display the total credits
    echo "<td>
            <a href='?edit=" . $row['course_id'] . "'>Edit</a> | 
            <a href='?delete=" . $row['course_id'] . "'>Delete</a>
          </td>";
    echo "</tr>";
}
echo "</table>";

// Handle delete
if (isset($_GET['delete'])) {
    $courseId = $_GET['delete'];
    $deleteStmt = $conn->prepare("DELETE FROM Course WHERE course_id = ?");
    $deleteStmt->bind_param("i", $courseId);
    if ($deleteStmt->execute()) {
        echo "Course deleted successfully.";
        header("Location: course.php"); // Redirect to avoid re-submission
        exit;
    } else {
        echo "Error: " . $deleteStmt->error;
    }
    $deleteStmt->close();
}
?>

<!-- Form for adding or editing a course -->
<form method="POST">
    <input type="hidden" name="course_id" value="<?php echo $courseToEdit['course_id'] ?? ''; ?>">
    <input type="text" name="course_name" placeholder="Course Name" value="<?php echo $courseToEdit['course_name'] ?? ''; ?>" required>
    <input type="text" name="budget" placeholder="Budget" value="<?php echo $courseToEdit['budget'] ?? ''; ?>" required>
    <input type="text" name="building" placeholder="Building" value="<?php echo $courseToEdit['building'] ?? ''; ?>" required>
    <input type="number" name="total_credits" placeholder="Total Credits" value="<?php echo $courseToEdit['total_credits'] ?? ''; ?>" required min="1">
    <button type="submit"><?php echo isset($courseToEdit) ? 'Update Course' : 'Add Course'; ?></button>
</form>

<!-- Back button -->
<button onclick="window.location.href='index.php';">Back</button>

<?php
// Close the connection
$conn->close();
?>

</body>
</html>
