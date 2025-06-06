<?php
include '../../connect.php';

// Delete logic
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    pg_query_params($conn, "DELETE FROM STUDENT WHERE stud_id = $1", [$delete_id]);
    header("Location: sciGrade7.php");
    exit;
}

// Assign or update section and teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_section'], $_POST['stud_id'])) {
    $section_id = (int) $_POST['assign_section'];
    $stud_id = (int) $_POST['stud_id'];

    // Get the teacher assigned to this section
    $teacher_result = pg_query_params($conn, "SELECT teach_no FROM TEACHER WHERE section_id = $1 LIMIT 1", [$section_id]);
    if ($teacher_result && pg_num_rows($teacher_result) > 0) {
        $teacher = pg_fetch_assoc($teacher_result);
        $teach_no = (int) $teacher['teach_no'];

        // Update student's section and teacher
        pg_query_params($conn, "
            UPDATE STUDENT 
            SET section_id = $1, teach_no = $2 
            WHERE stud_id = $3
        ", [$section_id, $teach_no, $stud_id]);

        if (in_array($teach_no, [6, 7, 8, 9, 10]) && in_array($section_id, [2, 3, 4, 5, 6])) {
            pg_query_params($conn, "
                UPDATE STUDENT 
                SET gradelvl_id = 7 
                WHERE stud_id = $1
            ", [$stud_id]);
        }
    }

    header("Location: sciGrade7Infos.php?id=" . $stud_id);
    exit;
}

// Get the student ID
$student_id = $_GET['id'] ?? null;
if (!$student_id) {
    header("Location: sciGrade7.php");
    exit;
}

// Fetch student
$query = "
    SELECT 
        s.stud_id,
        s.stud_fname,
        s.stud_lname,
        s.stud_midname,
        s.stud_email,
        s.stud_dob,
        s.stud_gender,
        s.stud_lrn,
        s.stud_phonenum,
        s.section_id,
        gl.gradelvl_name AS grade,
        CASE 
            WHEN gl.gradelvl_name IN ('Grade 11', 'Grade 12') THEN 'Senior High School'
            WHEN gl.gradelvl_name IS NOT NULL THEN 'Junior High School'
            ELSE 'N/A'
        END AS level,
        sec.section_name AS section
    FROM STUDENT s
    LEFT JOIN GRADE_LEVEL gl ON s.gradelvl_id = gl.gradelvl_id
    LEFT JOIN SECTION sec ON s.section_id = sec.section_id
    WHERE s.stud_id = $1
";

$result = pg_query_params($conn, $query, [$student_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Grade 7 SSC - Student Info</title>
    <link rel="stylesheet" href="studentList.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<header>
    <div class="head">
        <img src="../../images/header.png" alt="Website Header">
    </div>
    <div class="header-logos">
        <div class="logo-group left">
            <div class="logo"><img src="../../images/logo1.png" alt="Logo 1" /></div>
            <div class="logo"><img src="../../images/logo2.png" alt="Logo 2" /></div>
        </div>
        <div class="school-title">
            <h1>MAGDUGO<br><span>NATIONAL HIGHSCHOOL</span></h1>
        </div>
        <div class="logo-group right">
            <div class="logo"><img src="../../images/logo3.png" alt="Logo 3" /></div>
            <div class="logo"><img src="../../images/logo4.png" alt="Logo 4" /></div>
        </div>
    </div>
</header>

<section id="student-content">
    <div class="dashboard">
        <a href="adminhome.php"><h5>DASHBOARD</h5></a>
        <center>
            <div id="profile">
                <img src="../../images/prof.jpg" alt="profile" width="100" height="100">
            </div>
        </center>
        <ul class="button-list">
            <li><a href="adminhome.php" class="btn"><span>HOME</span></a></li>
            <li><a href="regularclass.php" class="btn"><span>JUNIOR CLASS</span></a></li>
            <li><a href="regularclassSenior.php" class="btn"><span>SENIOR CLASS</span></a></li>
            <li><a href="scienceClass.php" class="btn"><span>SCIENCE CLASS</span></a></li>
        </ul>
    </div>
</section>

<div class="search-bar">
    <form method="GET">
        <button class="db-btn" type="button" onclick="history.back()"><i class="fas fa-times"></i> Back</button>
    </form>
</div>

<div class="Info">
    <h1> Grade 7 SSC Student Information</h1>
</div>

<div class="cards-body">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Gender</th>
                <th>Age</th>
                <th>Level</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $stud_id = $row['stud_id'];
            $lname = htmlspecialchars($row['stud_lname']);
            $fname = htmlspecialchars($row['stud_fname']);
            $mname = htmlspecialchars($row['stud_midname']);
            $email = htmlspecialchars($row['stud_email']);
            $phone = htmlspecialchars($row['stud_phonenum']);
            $gender = htmlspecialchars($row['stud_gender']);
            $dob = $row['stud_dob'];
            $grade = htmlspecialchars($row['grade']);
            $level = htmlspecialchars($row['level']);
            $section = $row['section'];
            $section_id_current = $row['section_id'];

            $age = '';
            if ($dob) {
                $dobDate = new DateTime($dob);
                $now = new DateTime();
                $age = $now->diff($dobDate)->y;
            }

            echo "<tr>
                <td>$lname, $fname " . strtoupper(substr($mname, 0, 1)) . ".</td>
                <td>$email</td>
                <td>$phone</td>
                <td>$gender</td>
                <td>$age</td>
                <td>$level</td>
                <td>$grade</td>
                <td>";

            $sectionResult = pg_query($conn, "SELECT section_id, section_name FROM SECTION WHERE gradelvl_id IN (7, 13)");
            if ($sectionResult && pg_num_rows($sectionResult) > 0) {
                echo '<form method="POST" class="d-flex">';
                echo '<input type="hidden" name="stud_id" value="' . $stud_id . '">';
                echo '<select name="assign_section" class="form-select form-select-sm me-2" required>';
                echo '<option value="">Select</option>';
                while ($sec = pg_fetch_assoc($sectionResult)) {
                    $sid = $sec['section_id'];
                    $sname = htmlspecialchars($sec['section_name']);
                    $selected = ($section_id_current == $sid) ? 'selected' : '';
                    echo "<option value='$sid' $selected>$sname</option>";
                }
                echo '</select>';
                echo '<button type="submit" class="btn btn-success btn-sm">';
                echo $section ? 'Update Section' : 'Add Section';
                echo '</button>';
                echo '</form>';
            } else {
                echo 'No sections available.';
            }

            echo "</td>
                <td>
                    <a href='sciGrade7Infos.php?delete=$stud_id' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this student?\")'><i class='fas fa-trash-alt'></i></a>
                </td>
            </tr>";
        } else {
            echo "<tr><td colspan='9' class='text-center'>No student record found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
