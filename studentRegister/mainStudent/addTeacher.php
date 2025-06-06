<?php
require_once('../../connect.php'); // PostgreSQL connection

$gradeLevelMap = [
    7 => 'Grade 7',
    8 => 'Grade 8',
    9 => 'Grade 9',
    10 => 'Grade 10',
    11 => 'Grade 11',
    12 => 'Grade 12',
    13 => 'Grade 7 - SSC',
    14 => 'Grade 8 - SSC',
    15 => 'Grade 9 - SSC',
    16 => 'Grade 10 - SSC'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    $firstName = pg_escape_string($conn, $_POST['firstName']);
    $midIni = pg_escape_string($conn, $_POST['midIni']);
    $lastName = pg_escape_string($conn, $_POST['lastName']);
    $gradelvl_id = !empty($_POST['gradelvl_id']) ? (int)$_POST['gradelvl_id'] : NULL;
    $section = !empty($_POST['section']) ? pg_escape_string($conn, $_POST['section']) : NULL;
    $contactInfo = !empty($_POST['contactInfo']) ? pg_escape_string($conn, $_POST['contactInfo']) : NULL;

    // Check if the teacher already exists
    $check_query = "SELECT * FROM teacher WHERE teach_fname = '$firstName' AND teach_lname = '$lastName'";
    $check_result = pg_query($conn, $check_query);
    if (!$check_result) {
        die("Query failed: " . pg_last_error($conn));
    }

    if (pg_num_rows($check_result) > 0) {
        echo "<script>alert('Teacher Already Exists'); window.location.href = 'addTeacher.php';</script>";
    } else {

        $section_name = pg_escape_string($conn, $_POST['section']);
        $section_id = 'NULL';

        // Look up section_id by section_name
        $insert_section_query = "INSERT INTO section (section_name) VALUES ('$section_name') RETURNING section_id";
        $section_result = pg_query($conn, $insert_section_query);

        if ($section_result && pg_num_rows($section_result) > 0) {
            $row = pg_fetch_assoc($section_result);
            $section_id = $row['section_id'];
        }

        $insert_query = "INSERT INTO teacher (
            teach_fname, 
            teach_mid_initial, 
            teach_lname, 
            gradelvl_id, 
            section_id, 
            teach_phonenum
        ) VALUES (
            '$firstName',
            '$midIni',
            '$lastName',
            " . ($gradelvl_id ? $gradelvl_id : "NULL") . ",
            $section_id,
            " . ($contactInfo ? "'$contactInfo'" : "NULL") . "
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin</title>
  <link rel="stylesheet" href="teacherList.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>
<body>

<header>
  <div class="head">
    <img src="../../images/header.png" alt="Website Header" />
  </div>

  <div class="header-logos">
    <div class="logo-group left">
      <div class="logo"><img src="../../images/logo1.png" alt="Logo 1" /></div>
      <div class="logo"><img src="../../images/logo2.png" alt="Logo 2" /></div>
    </div>

    <div class="school-title">
      <h1>MAGDUGO<br /><span>NATIONAL HIGHSCHOOL</span></h1>
    </div>

    <div class="logo-group right">
      <div class="logo"><img src="../../images/logo3.png" alt="Logo 3" /></div>
      <div class="logo"><img src="../../images/logo4.png" alt="Logo 4" /></div>
    </div>
  </div>
</header>

<section id="teacher-content">
  <div class="dashboard">
    <a href="adminhome.php"><h5>DASHBOARD</h5></a>
    <center>
      <div id="profile">
        <img src="../../images/prof.jpg" alt="profile" width="100" height="100" />
      </div>
    </center>
    <ul class="button-list">
      <li><a href="adminhome.php" class="btn"><span>HOME</span></a></li>
      <li><a href="studentList.php" class="btn"><span>STUDENTS</span></a></li>
    </ul>
  </div>
</section>

<div class="search-bar">
  <form method="GET">
        <button class="db-btn" type="button" onclick="history.back()"><i class="fas fa-times"></i> Back</button>
  </form>
</div>

<div class="Info">
  <h1>Add New Teacher</h1>
</div>

    <section id="add-teacher-content">
        <div class="container">
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
                    <label for="gradelvl_id">Grade Level</label>
                    <select name="gradelvl_id" id="gradelvl_id" class="form-control" required>
                        <option value="">-- Select Grade Level --</option>
                        <?php 
                        foreach ($gradeLevelMap as $id => $label) {
                            echo "<option value=\"$id\">$label</option>";
                        }
                        ?>
                    </select>
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
            window.location.href = 'teacherList.php';
        }
    </script>
</body>
</html>
