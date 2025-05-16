<?php
require_once('../../connect.php'); // PostgreSQL connection

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    $firstName = pg_escape_string($conn, $_POST['firstName']);
    $midIni = pg_escape_string($conn, $_POST['midIni']);
    $lastName = pg_escape_string($conn, $_POST['lastName']);
    $gradeLevel = !empty($_POST['gradeLevel']) ? pg_escape_string($conn, $_POST['gradeLevel']) : NULL;
    $section = !empty($_POST['section']) ? pg_escape_string($conn, $_POST['section']) : NULL;
    $contactInfo = !empty($_POST['contactInfo']) ? pg_escape_string($conn, $_POST['contactInfo']) : NULL;

    // Static school_id for now (change if dynamic)
    $school_id = 303312;

    // Check if the teacher already exists
    $check_query = "SELECT * FROM teacher WHERE teach_fname = '$firstName' AND teach_lname = '$lastName'";
    $check_result = pg_query($conn, $check_query);
    if (!$check_result) {
        die("Query failed: " . pg_last_error($conn));
    }

    if (pg_num_rows($check_result) > 0) {
        echo "<script>alert('Teacher Already Exists'); window.location.href = 'addTeacher.php';</script>";
    } else {
        // Insert new teacher
        $insert_query = "INSERT INTO teacher (
            teach_fname, 
            teach_mid_initial, 
            teach_lname, 
            teach_gradelvl, 
            teach_section, 
            teach_phonenum, 
            school_id
        ) VALUES (
            '$firstName',
            '$midIni',
            '$lastName',
            " . ($gradeLevel ? "'$gradeLevel'" : "NULL") . ",
            " . ($section ? "'$section'" : "NULL") . ",
            " . ($contactInfo ? "'$contactInfo'" : "NULL") . ",
            $school_id
        )";

        if (pg_query($conn, $insert_query)) {
            header("Location: teacherList.php");
            exit();
        } else {
            echo "<div style='color: red;'>Error adding teacher: " . pg_last_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Teacher</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <img src="/image/logo2.png" alt="Logo" class="logo">
            <h1>MAGDUGO NATIONAL SCHOOL</h1>
        </div>
    </header>

    <section id="add-teacher-content">
        <div class="container">
            <h2>Add New Teacher</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                </div>
                <div class="form-group">
                    <label for="midIni">Middle Initial</label>
                    <input type="text" class="form-control" id="midIni" name="midIni" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                </div>
                <div class="form-group">
                    <label for="gradeLevel">Grade Level</label>
                    <input type="text" class="form-control" id="gradeLevel" name="gradeLevel">
                </div>
                <div class="form-group">
                    <label for="section">Section</label>
                    <input type="text" class="form-control" id="section" name="section">
                </div>
                <div class="form-group">
                    <label for="contactInfo">Contact Info</label>
                    <input type="text" class="form-control" id="contactInfo" name="contactInfo">
                </div>
                <button type="submit" name="add_teacher" class="btn btn-primary">Add Teacher</button>
                <button type="button" class="btn btn-secondary" onclick="redirectToTeacherList()">Cancel</button>
            </form>
        </div>
    </section>

    <script>
        function redirectToTeacherList() {
            window.location.href = 'teacherlist.php';
        }
    </script>
</body>
</html>
