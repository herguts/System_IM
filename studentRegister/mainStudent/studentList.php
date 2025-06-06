<?php
include '../../connect.php';

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    pg_query_params($conn, "DELETE FROM STUDENT WHERE stud_id = $1", [$delete_id]);
    header("Location: studentList.php");
    exit;
}

// Handle search
$search = $_GET['search'] ?? '';
$filter_grade = $_GET['filter_grade'] ?? '';

$clauses = [];
$params = [];
$index = 1;

if (!empty($search)) {
    $clauses[] = "(s.stud_fname || ' ' || s.stud_lname || ' ' || s.stud_midname) ILIKE $" . $index;
    $params[] = "%$search%";
    $index++;
}

if (!empty($filter_grade)) {
    $clauses[] = "s.gradelvl_id = $" . $index;
    $params[] = $filter_grade;
    $index++;
}

$search_sql = !empty($clauses) ? "WHERE " . implode(" AND ", $clauses) : "";

// Fetch enrollments
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
        s.gradelvl_id,  -- included for routing
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

        <!-- Grade Level Dropdown -->
        <select name="filter_grade" class="form-select d-inline w-auto mx-2">
            <option value="">All Grades</option>
            <?php
            $grade_res = pg_query($conn, "SELECT gradelvl_id, gradelvl_name FROM GRADE_LEVEL ORDER BY gradelvl_id ASC");
            while ($grade_row = pg_fetch_assoc($grade_res)) {
                $selected = ($filter_grade == $grade_row['gradelvl_id']) ? 'selected' : '';
                echo "<option value='{$grade_row['gradelvl_id']}' $selected>{$grade_row['gradelvl_name']}</option>";
            }
            ?>
        </select>

        <button class="db-btn" type="submit"><i class="fas fa-filter"></i> Filter</button>
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
                $strand = isset($row['strand']) ? htmlspecialchars($row['strand']) : '';
                $gradelvl_id = (int) $row['gradelvl_id'];

                // Calculate age
                $age = '';
                if ($dob) {
                    $dobDate = new DateTime($dob);
                    $now = new DateTime();
                    $age = $now->diff($dobDate)->y;
                }

                $displayLevel = $level;
                $displayStrand = ($level === 'Senior High School') ? $strand : '';

                // Determine the appropriate info page
                switch ($gradelvl_id) {
                    case 7:  $infoPage = 'regGrade7Infos.php'; break;
                    case 8:  $infoPage = 'regGrade8Infos.php'; break;
                    case 9:  $infoPage = 'regGrade9Infos.php'; break;
                    case 10: $infoPage = 'regGrade10Infos.php'; break;
                    case 11: $infoPage = 'regGrade11Infos.php'; break;
                    case 12: $infoPage = 'regGrade12Infos.php'; break;
                    case 13: $infoPage = 'sciGrade7Infos.php'; break;
                    case 14: $infoPage = 'sciGrade8Infos.php'; break;
                    case 15: $infoPage = 'sciGrade9Infos.php'; break;
                    case 16: $infoPage = 'sciGrade10Infos.php'; break;
                    default: $infoPage = ''; break;
                }

                echo "<tr>
                    <td>$lname, $fname " . strtoupper(substr($mname, 0, 1)) . ".</td>
                    <td>$email</td>
                    <td>$phone</td>
                    <td>$gender</td>
                    <td>$age</td>
                    <td>$displayLevel</td>
                    <td>$grade</td>
                    <td>$displayStrand</td>
                    <td>";
                if (!empty($infoPage)) {
                    echo "<a href='$infoPage?id=$stud_id' class='btn btn-info'><i class='fas fa-eye'></i></a>";
                } else {
                    echo "<span class='text-muted'>No Info Page</span>";
                }
                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='11' class='text-center'>No enrollment records found.</td></tr>";
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
