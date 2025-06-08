<?php
include '../../connect.php';


// Handle search
$search = $_GET['search'] ?? '';
$params = [];
$search_sql = "WHERE gl.gradelvl_id = 7";

if (!empty($search)) {
    $search = trim($search);
    $search_sql .= " AND (s.stud_fname ILIKE $1 OR s.stud_lname ILIKE $1 OR s.stud_phonenum ILIKE $1 OR s.stud_email ILIKE $1)";
    $params[] = "%$search%";
}

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
        gl.gradelvl_name AS grade,
        CASE 
            WHEN gl.gradelvl_name IN ('Grade 11', 'Grade 12') THEN 'Senior High School'
            WHEN gl.gradelvl_name IS NOT NULL THEN 'Junior High School'
            ELSE 'N/A'
        END AS level,
        str.strand_name AS strand
    FROM STUDENT s
    LEFT JOIN GRADE_LEVEL gl ON s.gradelvl_id = gl.gradelvl_id
    LEFT JOIN SECTION sec ON s.section_id = sec.section_id
    LEFT JOIN STRAND str ON sec.strand_id = str.strand_id
    $search_sql
";


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
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (performance.navigation.type === 2)) {
                location.reload();
            }
        });
    </script>
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
        <i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <button class="db-btn" type="button" onclick="window.location.href='regGrade7Report.php'"><i class="fas fa-chart-bar"></i> Report</button>
        <button class="db-btn" type="button" onclick="redirectToAddStudent()"><i class="fas fa-plus"></i> Add Student</button>
        <button class="db-btn" type="button" onclick="redirectToDashboard()"><i class="fas fa-times"></i> Close</button>
    </form>
</div>

<div class="Info">
    <h1> Grade 7 Student List</h1>
</div>

<!-- Remove any inline styles and keep it simple -->
<div class="container">
    <div class="cards-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>LRN</th>
                    <th>Grade</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && pg_num_rows($result) > 0) {
                    while ($row = pg_fetch_assoc($result)) {
                        $stud_id = $row['stud_id'];
                        $lname = htmlspecialchars($row['stud_lname']);
                        $fname = htmlspecialchars($row['stud_fname']);
                        $mname = htmlspecialchars($row['stud_midname']);
                        $gender = htmlspecialchars($row['stud_gender']);
                        $grade = htmlspecialchars($row['grade']);

                        echo "<tr>
                                <td>$lname, $fname " . strtoupper(substr($mname, 0, 1)) . ".</td>
                                <td>$gender</td>
                                <td>{$row['stud_lrn']}</td>
                                <td>$grade</td>
                                <td>
                                    <a href='regGrade7Infos.php?id=$stud_id' class='btn btn-info'><i class='fas fa-eye'></i></a>
                                    </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No enrollment records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
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
