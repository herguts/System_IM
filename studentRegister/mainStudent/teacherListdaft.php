<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
  <link rel="stylesheet" href="teacherList.css">
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

<section id="teacher-content">
  <div class="dashboard">
    <a href="adminhome.php"><h5>DASHBOARD</h5></a>
    <center>
      <div id="profile">
        <img src="../../images/prof.jpg" alt="profile" width="100" height="100">
      </div>
    </center>
    <ul class="button-list">
      <li><a href="studentList.php" class="btn"><span>STUDENTS</span></a></li>
      <li><a href="regularclass.php" class="btn"><span>REGULAR CLASS</span></a></li>
      <li><a href="scienceClass.php" class="btn"><span>SCIENCE CLASS</span></a></li>
    </ul>
  </div>
</section>

<div class="search-bar">
  <form>
    <i class="fas fa-search"></i>
    <input type="text" placeholder="Search...">
    <button class="db-btn" type="button" onclick="redirectToAddTeacher()"><i class="fas fa-plus"></i> Add Teacher</button>
    <button class="db-btn" type="button" onclick="redirectToDashboard()"><i class="fas fa-times"></i> Close</button>
  </form>
</div>

<div class="Info">
  <h1>Teacher List</h1>
</div>

<div class="card-body">
  <table>
    <thead>
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
      <!-- Replace with loop in backend -->
      <tr id="teacher_1">
        <td>1</td>
        <td class="editable">Juan D. Dela Cruz</td>
        <td class="editable">Grade 6</td>
        <td class="editable">Section A</td>
        <td class="editable">09123456789</td>
        <td class="action-buttons">
          <button class="btn btn-primary" onclick="editRow(1)"><i class="fas fa-pen"></i></button>
          <a class="btn btn-danger" href="#"><i class="fas fa-trash-alt"></i></a>
        </td>
      </tr>
      <tr id="edit_teacher_1" style="display:none;">
        <td colspan="6">
          <form>
            <input type="hidden" value="1">
            <div class="form-group"><label>First Name</label><input type="text" class="form-control" value="Juan"></div>
            <div class="form-group"><label>Middle Initial</label><input type="text" class="form-control" value="D"></div>
            <div class="form-group"><label>Last Name</label><input type="text" class="form-control" value="Dela Cruz"></div>
            <div class="form-group"><label>Grade Level</label><input type="text" class="form-control" value="Grade 6"></div>
            <div class="form-group"><label>Section</label><input type="text" class="form-control" value="Section A"></div>
            <div class="form-group"><label>Contact Info</label><input type="text" class="form-control" value="09123456789"></div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
            <button type="button" class="btn btn-secondary" onclick="cancelEdit(1)"><i class="fas fa-times"></i> Cancel</button>
          </form>
        </td>
      </tr>
    </tbody>
  </table>
</div>


<script>
  function redirectToDashboard() {
    window.location.href = 'adminhome.php';
  }

  function redirectToAddTeacher() {
    window.location.href = 'addteacher.php';
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