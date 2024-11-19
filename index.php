<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->
</head>
<body>
    <header>
        <h1>University Management System</h1>
        <link rel="stylesheet" href="style.css">

    </header>
    <nav>
        <ul>
            <li><a href="instructor.php">Manage Instructors</a></li>
            <li><a href="department.php">Manage Departments</a></li>
            <li><a href="course.php">Manage Courses</a></li>
            <li><a href="classroom.php">Manage Classrooms</a></li>
            <li><a href="timeslot.php">Manage Time Slots</a></li>
            <li><a href="student.php">Manage Students</a></li>
        </ul>
    </nav>
    <main>
        <h2>Welcome to the University Management System</h2>
        <p>Select an option from the menu to manage your data.</p>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> University Management System</p>
    </footer>
</body>
</html>
