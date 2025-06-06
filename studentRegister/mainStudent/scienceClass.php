<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin</title>
  <link rel="stylesheet" href="scienceClass.css" />
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

  <section id="class-content">
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
      </ul>
    </div>
  </section>

  <div id="dashboard-cards">
    <a href="sciGrade7.php" style="text-decoration: none; color: inherit;">
      <div class="card" style="background-color: rgb(160, 6, 6);">
        <h5>GRADE 7</h5>
        <div class="content-icon"><i class="fa fa-list" style="border: 4px solid; padding:4px;"></i></div>
      </div>
    </a>
    <a href="sciGrade8.php" style="text-decoration: none; color: inherit;">
      <div class="card" style="background-color: rgb(160, 6, 6);">
        <h5>GRADE 8</h5>
        <div class="content-icon"><i class="fa fa-list" style="border: 4px solid; padding:4px;"></i></div>
      </div>
    </a>
    <a href="sciGrade9.php" style="text-decoration: none; color: inherit;">
      <div class="card" style="background-color: rgb(160, 6, 6);">
        <h5>GRADE 9</h5>
        <div class="content-icon"><i class="fa fa-list" style="border: 4px solid; padding:4px;"></i></div>
      </div>
    </a>
    <a href="sciGrade10.php" style="text-decoration: none; color: inherit;">
      <div class="card" style="background-color: rgb(160, 6, 6);">
        <h5>GRADE 10</h5>
        <div class="content-icon"><i class="fa fa-list" style="border: 4px solid; padding:4px;"></i></div>
      </div>
    </a>

  <script>
    function toggleClassPanel() {
      var adminPanel = document.getElementById('admin-panel');
      var classPanel = document.getElementById('class-panel');
      adminPanel.classList.remove('show');
      classPanel?.classList.toggle('show');
    }

    document.addEventListener('DOMContentLoaded', function () {
      var dropdownToggle = document.getElementById('dropdownMenuLink');
      var settingsDropdown = document.getElementById('settingsDropdown');

      dropdownToggle.addEventListener('click', function () {
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