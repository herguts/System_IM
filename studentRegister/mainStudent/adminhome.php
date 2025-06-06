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
      <div class="card" style="background-color: rgb(21, 115, 152) ;">
        <h5>TEACHERS</h5>
        <div class="content-icon"><i class="fa fa-chalkboard-teacher"></i></div>
      </div>
    </a>
    <a href="studentList.php">
      <div class="card" style="background-color:rgb(21, 115, 152) ;">
        <h5>STUDENTS</h5>
        <div class="content-icon"><i class="fa fa-user-graduate"></i></div>
      </div>
    </a>
    <a href="regularclass.php">
      <div class="card" style="background-color:rgb(21, 115, 152) ;">
        <h5>JUNIOR CLASS</h5>
        <div class="content-icon"><i class="fa fa-book"></i></div>
      </div>
    </a>
    <a href="regularclassSenior.php">
      <div class="card" style="background-color:rgb(21, 115, 152) ;">
        <h5>SENIOR CLASS</h5>
        <div class="content-icon"><i class="fa fa-book"></i></div>
      </div>
    </a>
    <a href="scienceClass.php">
      <div class="card" style="background-color:rgb(21, 115, 152) ;">
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
        <li><a href="#" onclick="confirmLogout()">Logout</a></li>
      </ul>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div id="logoutModal" class="modal">
  <div class="modal-content">
    <p>Are you sure you want to logout?</p>
    <div class="modal-buttons">
      <button onclick="proceedLogout()">Yes</button>
      <button onclick="closeModal()">Cancel</button>
    </div>
  </div>
</div>

<script>
  // Toggle admin panel (existing function)
  function toggleAdminPanel() {
    var adminPanel = document.getElementById('admin-panel');
    adminPanel.classList.toggle('show');
  }

  // Show logout confirmation
  function confirmLogout() {
    document.getElementById('logoutModal').style.display = 'block';
  }

  // Close modal
  function closeModal() {
    document.getElementById('logoutModal').style.display = 'none';
  }

  // Proceed with logout
  function proceedLogout() {
    window.location.href = 'admin_login.php';
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    var modal = document.getElementById('logoutModal');
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  }
</script>

<style>
  /* Modal Styles */
  .modal {
    display: none;
    position: fixed;
    z-index: 1001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
  }

  .modal-content {
    background-color:rgb(21, 115, 152);
    margin: 15% auto;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  .modal-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 10px;
  }

  .modal-buttons button {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
  }

  .modal-buttons button:first-child {
    background-color: #ff5252;
    color: white;
  }

  .modal-buttons button:last-child {
    background-color: #e0e0e0;
  }
</style>

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