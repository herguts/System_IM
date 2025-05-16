<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin</title>
  <link rel="stylesheet" href="adminhome.css" />
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

  <section id="main-content">
    <div class="dashboard">
      <h5>DASHBOARD</h5>
      <center>
        <div id="profile">
          <img src="../../images/prof.jpg" alt="profile" width="100" height="100">
        </div>
        <button class="adminbtn" onclick="toggleAdminPanel()"><span>ADMIN</span></button>
      </center>
    </div>
  </section>

  <div id="dashboard-cards">
    <a href="teacherList.php">
      <div class="card" style="background-color: #FF0000;">
        <h5>TEACHERS</h5>
        <div class="content-icon"><i class="fa fa-chalkboard-teacher"></i></div>
      </div>
    </a>
    <a href="studentList.php">
      <div class="card" style="background-color:#32cd32;">
        <h5>STUDENTS</h5>
        <div class="content-icon"><i class="fa fa-user-graduate"></i></div>
      </div>
    </a>
    <a href="regularclass.php">
      <div class="card" style="background-color:#0000FF;">
        <h5>REGULAR CLASS</h5>
        <div class="content-icon"><i class="fa fa-book"></i></div>
      </div>
    </a>
    <a href="scienceClass.php">
      <div class="card" style="background-color:#0000FF;">
        <h5>SCIENCE CLASS</h5>
        <div class="content-icon"><i class="fa fa-book"></i></div>
      </div>
    </a>
  </div>

  <div class="admin-panel" id="admin-panel">
    <div class="admin-sidebar">
      <div class="admin-sidebar-header">
        <h4>ADMIN</h4><hr>
        <i class="fas fa-times-circle" onclick="toggleAdminPanel()"></i>
      </div>
      <div class="admin-sidebar-content">
        <ul>
            <li><a href="schoolSetting.php">School Settings</a></li>
          <li><a href="../../home.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <script>
    function toggleAdminPanel() {
      var adminPanel = document.getElementById('admin-panel');
      adminPanel.classList.toggle('show');
    }

    document.addEventListener('DOMContentLoaded', function () {
      var dropdownToggle = document.getElementById('dropdownMenuLink');
      var settingsDropdown = document.getElementById('settingsDropdown');

      dropdownToggle.addEventListener('click', function (e) {
        e.preventDefault();
        settingsDropdown.classList.toggle('show');
      });

      window.addEventListener('click', function (event) {
        if (!dropdownToggle.contains(event.target)) {
          settingsDropdown.classList.remove('show');
        }
      });
    });
  </script>
</body>
</html>