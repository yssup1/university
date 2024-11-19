<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
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
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $email = $_POST['email'] ?? '';
    $studentId = $_POST['student_id'] ?? null;

    // Use prepared statements to prevent SQL injection
    if ($studentId) {
        // Update existing student
        $stmt = $conn->prepare("UPDATE Student SET first_name = ?, last_name = ?, date_of_birth = ?, email = ? WHERE student_id = ?");
        $stmt->bind_param("ssssi", $firstName, $lastName, $dateOfBirth, $email, $studentId);
    } else {
        // Insert new student
        $stmt = $conn->prepare("INSERT INTO Student (first_name, last_name, date_of_birth, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstName, $lastName, $dateOfBirth, $email);
    }

    if ($stmt->execute()) {
        echo "Student saved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Read students (without displaying student_id)
$result = $conn->query("SELECT first_name, last_name, date_of_birth, email FROM Student");

// Display students (no ID column)
echo "<h2>Students</h2>";
echo "<table border='1'>";
echo "<tr><th>First Name</th><th>Last Name</th><th>Date of Birth</th><th>Email</th><th>Actions</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['first_name'] . "</td>";
    echo "<td>" . $row['last_name'] . "</td>";
    echo "<td>" . $row['date_of_birth'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>
            <a href='?edit=" . $row['email'] . "'>Edit</a> | 
            <a href='?delete=" . $row['email'] . "'>Delete</a>
          </td>";
    echo "</tr>";
}
echo "</table>";

// Handle delete
if (isset($_GET['delete'])) {
    $email = $_GET['delete'];
    $deleteStmt = $conn->prepare("DELETE FROM Student WHERE email = ?");
    $deleteStmt->bind_param("s", $email);
    if ($deleteStmt->execute()) {
        echo "Student deleted successfully.";
        header("Location: student.php"); // Redirect to avoid re-submission
        exit;
    } else {
        echo "Error: " . $deleteStmt->error;
    }
    $deleteStmt->close();
}

// Edit student
$studentToEdit = null;
if (isset($_GET['edit'])) {
    $email = $_GET['edit'];
    $editStmt = $conn->prepare("SELECT * FROM Student WHERE email = ?");
    $editStmt->bind_param("s", $email);
    $editStmt->execute();
    $studentToEdit = $editStmt->get_result()->fetch_assoc();
    $editStmt->close();
}
?>

<!-- Form for adding or editing a student -->
<form method="POST">
    <input type="hidden" name="student_id" value="<?php echo $studentToEdit['student_id'] ?? ''; ?>">
    <input type="text" name="first_name" placeholder="First Name" value="<?php echo $studentToEdit['first_name'] ?? ''; ?>" required>
    <input type="text" name="last_name" placeholder="Last Name" value="<?php echo $studentToEdit['last_name'] ?? ''; ?>" required>
    <input type="date" name="date_of_birth" placeholder="Date of Birth" value="<?php echo $studentToEdit['date_of_birth'] ?? ''; ?>" required>
    <input type="email" name="email" placeholder="Email" value="<?php echo $studentToEdit['email'] ?? ''; ?>" required>
    <button type="submit"><?php echo isset($studentToEdit) ? 'Update Student' : 'Add Student'; ?></button>
</form>

<!-- Back button -->
<button onclick="window.location.href='index.php';">Back</button>

<?php
// Close the connection
$conn->close();
?>

</body>
</html>
