<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Slot Management</title>
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
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $day = $_POST['day'];  // New field for day of the week
    $timeSlotId = $_POST['time_slot_id'] ?? null;

    // Use prepared statements to prevent SQL injection
    if ($timeSlotId) {
        // Update existing time slot
        $stmt = $conn->prepare("UPDATE TimeSlot SET start_time = ?, end_time = ?, day = ? WHERE time_slot_id = ?");
        $stmt->bind_param("sssi", $startTime, $endTime, $day, $timeSlotId);
    } else {
        // Insert new time slot
        $stmt = $conn->prepare("INSERT INTO TimeSlot (start_time, end_time, day) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $startTime, $endTime, $day);
    }

    if ($stmt->execute()) {
        echo "Time slot saved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Read time slots
$result = $conn->query("SELECT time_slot_id, start_time, end_time, day FROM TimeSlot");

// Display time slots without the ID column
echo "<h2>Time Slots</h2>";
echo "<table border='1'>";
echo "<tr><th>Day</th><th>Start Time</th><th>End Time</th><th>Actions</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['day'] . "</td>";
    echo "<td>" . $row['start_time'] . "</td>";
    echo "<td>" . $row['end_time'] . "</td>";
    echo "<td>
            <a href='?edit=" . $row['time_slot_id'] . "'>Edit</a> | 
            <a href='?delete=" . $row['time_slot_id'] . "'>Delete</a>
          </td>";
    echo "</tr>";
}
echo "</table>";

// Handle delete
if (isset($_GET['delete'])) {
    $timeSlotId = $_GET['delete'];
    $deleteStmt = $conn->prepare("DELETE FROM TimeSlot WHERE time_slot_id = ?");
    $deleteStmt->bind_param("i", $timeSlotId);
    if ($deleteStmt->execute()) {
        echo "Time slot deleted successfully.";
        header("Location: timeslot.php"); // Redirect to avoid re-submission
        exit;
    } else {
        echo "Error: " . $deleteStmt->error;
    }
    $deleteStmt->close();
}

// Edit time slot
$timeSlotToEdit = null;
if (isset($_GET['edit'])) {
    $timeSlotId = $_GET['edit'];
    $editStmt = $conn->prepare("SELECT * FROM TimeSlot WHERE time_slot_id = ?");
    $editStmt->bind_param("i", $timeSlotId);
    $editStmt->execute();
    $timeSlotToEdit = $editStmt->get_result()->fetch_assoc();
    $editStmt->close();
}
?>

<!-- Form for adding or editing a time slot -->
<form method="POST">
    <input type="hidden" name="time_slot_id" value="<?php echo $timeSlotToEdit['time_slot_id'] ?? ''; ?>">
    
    <!-- Dropdown for selecting day -->
    <select name="day" required>
        <option value="">Select Day</option>
        <option value="Monday" <?php echo (isset($timeSlotToEdit) && $timeSlotToEdit['day'] == 'Monday') ? 'selected' : ''; ?>>Monday</option>
        <option value="Tuesday" <?php echo (isset($timeSlotToEdit) && $timeSlotToEdit['day'] == 'Tuesday') ? 'selected' : ''; ?>>Tuesday</option>
        <option value="Wednesday" <?php echo (isset($timeSlotToEdit) && $timeSlotToEdit['day'] == 'Wednesday') ? 'selected' : ''; ?>>Wednesday</option>
        <option value="Thursday" <?php echo (isset($timeSlotToEdit) && $timeSlotToEdit['day'] == 'Thursday') ? 'selected' : ''; ?>>Thursday</option>
        <option value="Friday" <?php echo (isset($timeSlotToEdit) && $timeSlotToEdit['day'] == 'Friday') ? 'selected' : ''; ?>>Friday</option>
        <option value="Saturday" <?php echo (isset($timeSlotToEdit) && $timeSlotToEdit['day'] == 'Saturday') ? 'selected' : ''; ?>>Saturday</option>
        <option value="Sunday" <?php echo (isset($timeSlotToEdit) && $timeSlotToEdit['day'] == 'Sunday') ? 'selected' : ''; ?>>Sunday</option>
    </select>
    
    <input type="time" name="start_time" placeholder="Start Time" value="<?php echo $timeSlotToEdit['start_time'] ?? ''; ?>" required>
    <input type="time" name="end_time" placeholder="End Time" value="<?php echo $timeSlotToEdit['end_time'] ?? ''; ?>" required>
    <button type="submit"><?php echo isset($timeSlotToEdit) ? 'Update Time Slot' : 'Add Time Slot'; ?></button>
</form>

<!-- Back button -->
<button onclick="window.location.href='index.php';">Back</button>

<?php
// Close the connection
$conn->close();
?>

</body>
</html>
