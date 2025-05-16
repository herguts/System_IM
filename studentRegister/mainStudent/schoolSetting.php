<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>School Settings</title>
  <link rel="stylesheet" href="schoolSetting.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
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

  <section id="user-content">
    <div class="dashboard p-3">
      <a href="adminhome.php"><h5>DASHBOARD</h5></a>
      <center>
        <div id="profile">
          <img src="../../images/prof.jpg" alt="profile" width="100" height="100"/>
        </div>
        <button class="adminbtn mt-2" onclick="toggleAdminPanel()"><span>ADMIN</span></button>
      </center>
    </div>
  </section>

  <div class="title mt-4 text-center">
    <h4>SCHOOL SETTINGS</h4>
  </div>

  <div class="tab container mt-4">
    <h5>School Information</h5>
    <form id="edit-school-form">
      <input type="hidden" name="school_id" value="123456">
      <label for="school-id" class="form-label">School ID:</label>
      <input type="text" class="form-control" id="school-id" value="123456" disabled>

      <label for="school-name" class="form-label">School Name:</label>
      <input type="text" class="form-control" id="school-name" name="school_name" value="DON ANDRES SORIANO ELEMENTARY SCHOOL" readonly>

      <label for="school-region" class="form-label">Region:</label>
      <input type="text" class="form-control" id="school-region" name="region" value="Region VII" readonly>

      <label for="school-division" class="form-label">Division:</label>
      <input type="text" class="form-control" id="school-division" name="division" value="Toledo City" readonly>

      <label for="school-address" class="form-label">Address:</label>
      <textarea class="form-control" id="school-address" name="school_address" rows="3" readonly>Don Andres Soriano, Toledo City</textarea>

      <div class="form-actions mt-3">
        <button type="button" id="edit-btn" class="btn btn-warning" onclick="enableEdit()">Edit</button>
        <button type="submit" id="save-btn" class="btn btn-success" style="display: none;">Save Changes</button>
        <button type="button" id="cancel-btn" class="btn btn-secondary" onclick="cancelEdit()" style="display: none;">Cancel</button>
      </div>
    </form>
  </div>

  <!-- Admin Panel Sidebar -->
  <div class="admin-panel" id="admin-panel" style="display: none;">
    <div class="admin-sidebar p-3 bg-light border-start position-fixed end-0 top-0 h-100 shadow" style="width: 250px;">
      <div class="admin-sidebar-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">ADMIN</h4>
        <i class="fas fa-times-circle fs-5 cursor-pointer" onclick="toggleAdminPanel()"></i>
      </div>
      <hr>
      <div class="admin-sidebar-content">
        <ul class="list-unstyled">
          <li class="dropdown">
            <a class="dropdown-toggle" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
              Settings
            </a>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink" id="settingsDropdown">
              <li><a class="dropdown-item" href="usersetting.php">User Settings</a></li>
              <li><a class="dropdown-item" href="schoolsettings.php">School Settings</a></li>
            </ul>
          </li>
          <li><a href="../../home.php" class="d-block mt-2">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <script>
    function toggleAdminPanel() {
      const panel = document.getElementById('admin-panel');
      panel.style.display = panel.style.display === 'none' || panel.style.display === '' ? 'block' : 'none';
    }

    function enableEdit() {
      ['school-name', 'school-region', 'school-division', 'school-address'].forEach(id => {
        document.getElementById(id).readOnly = false;
      });
      document.getElementById('edit-btn').style.display = 'none';
      document.getElementById('save-btn').style.display = 'inline-block';
      document.getElementById('cancel-btn').style.display = 'inline-block';
    }

    function cancelEdit() {
      ['school-name', 'school-region', 'school-division', 'school-address'].forEach(id => {
        document.getElementById(id).readOnly = true;
      });
      document.getElementById('edit-btn').style.display = 'inline-block';
      document.getElementById('save-btn').style.display = 'none';
      document.getElementById('cancel-btn').style.display = 'none';
    }
  </script>
</body>
</html>