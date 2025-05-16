<?php
include '../../connect.php';

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $delete_query = "DELETE FROM enrollments WHERE enrollment_id = $1";
    pg_query_params($conn, $delete_query, [$delete_id]);
    header("Location: studentList.php");
    exit;
}

// Handle search
$search = $_GET['search'] ?? '';
$search_sql = "";
$params = [];

if (!empty($search)) {
    $search = trim($search);
    $search_sql = "WHERE full_name ILIKE $1 OR email ILIKE $1 OR phone ILIKE $1";
    $params[] = "%$search%";
}

// Fetch enrollments
$query = "SELECT * FROM enrollments $search_sql";
$result = pg_query_params($conn, $query, $params);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Enrollments</title>
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
            <li><a href="teacherList.php" class="btn"><span>TEACHERS</span></a></li>
            <li><a href="regularclass.php" class="btn"><span>REGULAR CLASS</span></a></li>
            <li><a href="scienceClass.php" class="btn"><span>SCIENCE CLASS</span></a></li>
        </ul>
    </div>
</section>

<div class="search-bar">
    <form method="GET">
        <i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <button class="db-btn" type="button" onclick="redirectToAddStudent()"><i class="fas fa-plus"></i> Add Student</button>
        <button class="db-btn" type="button" onclick="redirectToDashboard()"><i class="fas fa-times"></i> Close</button>
    </form>
</div>

<div class="Info">
    <h1>Student List</h1>
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
            <th>Strand</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                // $id = $row['enrollment_id'];
                $name = htmlspecialchars($row['full_name']);
                $email = htmlspecialchars($row['email']);
                $phone = htmlspecialchars($row['phone']);
                $gender = htmlspecialchars($row['gender']);
                $dob = $row['dob'];
                $level = htmlspecialchars($row['level']);
                $grade = htmlspecialchars($row['senior_grade']);
                $strand = htmlspecialchars($row['grade_strand']);

                // Calculate age from DOB
                $age = '';
                if ($dob) {
                    $dobDate = new DateTime($dob);
                    $now = new DateTime();
                    $age = $now->diff($dobDate)->y;
                }

                if ($level === 'Senior High School') {
                    $displayLevel = "Grade $senior_grade";
                    $displayStrand = $strand;
                } else {
                    $displayLevel = $level; // Already like "Grade 9", "Grade 10"
                    $displayStrand = ' '; // No strand for juniors
                }

                echo "<tr>
                        <td>$name</td>
                        <td>$email</td>
                        <td>$phone</td>
                        <td>$gender</td>
                        <td>$age</td>
                        <td>$level</td>
                        <td>$grade</td>
                        <td>$strand</td>
                            <td>
                                <a href='viewEnrollment.php?id=$name' class='btn btn-info'><i class='fas fa-info-circle'></i></a>
                                <a href='editEnrollment.php?id=$name' class='btn btn-warning'><i class='fas fa-pen'></i></a>
                                <a href='studentList.php?delete=$name' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this student?\")'><i class='fas fa-trash-alt'></i></a>
                            </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='10' class='text-center'>No enrollment records found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<script>
    function redirectToDashboard() {
        window.location.href = "adminhome.php";
    }

    function redirectToAddStudent() {
        window.location.href = "addStudent.php";
    }
</script>
</body>
</html>
