<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET') {
    require '../../connect.php';

    if (!$conn) {
        die("Database connection failed.");
    }

    // Search functionality
    $search_query = "";
    if (isset($_GET['search'])) {
        $search = pg_escape_string($conn, $_GET['search']);
        $search_query = "WHERE CAST(teach_no AS TEXT) ILIKE '%$search%' 
            OR teach_lname ILIKE '%$search%' 
            OR teach_fname ILIKE '%$search%'";
    }

    // Modified query to join section table for section_name
    $query = "SELECT t.*, s.section_name FROM teacher t
              LEFT JOIN section s ON t.section_id = s.section_id
              $search_query ORDER BY teach_no";
    $result = pg_query($conn, $query);

    if (!$result) {
        echo "<div style='color: white; background: red; padding: 10px;'>Query failed: " . pg_last_error($conn) . "</div>";
        exit;
    }

    // Delete teacher
    if (isset($_GET['delete_id'])) {
        $delete_id = pg_escape_string($conn, $_GET['delete_id']);
        $delete_query = "DELETE FROM teacher WHERE teach_no = '$delete_id'";
        if (pg_query($conn, $delete_query)) {
            header("Location: teacherList.php");
            exit();
        } else {
            echo "Error deleting record: " . pg_last_error($conn);
        }
    }

    // Update teacher - ONLY grade level editable now
    if (isset($_POST['update_teacher'])) {
        $teach_no = pg_escape_string($conn, $_POST['teach_no']);
        $gradelvl_id = pg_escape_string($conn, $_POST['gradelvl_id']);

        $update_query = "UPDATE teacher SET gradelvl_id = '$gradelvl_id' WHERE teach_no = '$teach_no'";
        if (pg_query($conn, $update_query)) {
            header("Location: teacherList.php");
            exit();
        } else {
            echo "Error updating record: " . pg_last_error($conn);
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
    <i class="fas fa-search"></i>
    <input type="text" name="search" placeholder="Search...">
    <button class="db-btn" type="submit"><i class="fas fa-search"></i> Go</button>
    <button class="db-btn" type="button" onclick="redirectToAddTeacher()"><i class="fas fa-plus"></i> Add Teacher</button>
    <button class="db-btn" type="button" onclick="redirectToDashboard()"><i class="fas fa-times"></i> Close</button>
  </form>
</div>

<div class="Info">
  <h1>Teacher List</h1>
</div>

<div class="card-body table-responsive">
  <table class="table table-striped table-bordered align-middle">
    <thead class="table-secondary">
      <tr>
        <th>Teach_No</th>
        <th>Name</th>
        <th>Grade Level</th>
        <th>Section</th>
        <th>Contact Info</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
        <?php 
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

        while ($row = pg_fetch_assoc($result)) { 
            $gradeDesc = isset($gradeLevelMap[$row['gradelvl_id']]) ? $gradeLevelMap[$row['gradelvl_id']] : $row['gradelvl_id'];
            $fullName = htmlspecialchars($row['teach_fname']) . ' ' . htmlspecialchars($row['teach_mid_initial']) . '. ' . htmlspecialchars($row['teach_lname']);
            $sectionName = htmlspecialchars($row['section_name']);
        ?>
        <tr id="teacher_<?php echo $row['teach_no']; ?>">
          <td><?php echo $row['teach_no']; ?></td>
          <td><?php echo $fullName; ?></td>
          <td><?php echo $gradeDesc; ?></td>
          <td><?php echo $sectionName; ?></td>
          <td><?php echo htmlspecialchars($row['teach_phonenum']); ?></td>
          <td>
            <div class="btn-group" role="group">
              <button class="btn btn-primary btn-sm" onclick="editRow(<?php echo $row['teach_no']; ?>)">
                <i class="fas fa-edit"></i> Edit
              </button>
              <a href="?delete_id=<?php echo $row['teach_no']; ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm">
                <i class="fas fa-trash-alt"></i> Delete
              </a>
            </div>
          </td>
        </tr>

        <!-- Edit row: only grade level editable -->
        <tr id="edit_teacher_<?php echo $row['teach_no']; ?>" style="display:none;">
          <td colspan="6">
            <form method="POST" action="" class="d-flex align-items-center gap-2">
              <input type="hidden" name="teach_no" value="<?php echo $row['teach_no']; ?>" />
              <label class="mb-0 fw-bold">Edit Grade Level:</label>
                <select name="gradelvl_id" required>
                  <?php 
                  foreach ($gradeLevelMap as $id => $desc) {
                      $selected = ($row['gradelvl_id'] == $id) ? "selected" : "";
                      echo "<option value=\"$id\" $selected>$desc</option>";
                  }
                  ?>
                </select>
              <button type="submit" name="update_teacher" class="btn btn-success btn-sm">
                <i class="fas fa-check"></i> Save
              </button>
              <button type="button" onclick="cancelEdit(<?php echo $row['teach_no']; ?>)" class="btn btn-secondary btn-sm">
                <i class="fas fa-times"></i> Cancel
              </button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script>
function redirectToDashboard() {
  window.location.href = 'adminhome.php';
}
function redirectToAddTeacher() {
  window.location.href = 'addTeacher.php';
}
function editRow(id) {
  document.getElementById('teacher_' + id).style.display = 'none';
  document.getElementById('edit_teacher_' + id).style.display = 'table-row';
}
function cancelEdit(id) {
  document.getElementById('teacher_' + id).style.display = 'table-row';
  document.getElementById('edit_teacher_' + id).style.display = 'none';
}
</script>

</body>
</html>
