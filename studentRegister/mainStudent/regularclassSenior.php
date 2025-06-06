<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="regularclass.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
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
            <li><a href="scienceClass.php" class="btn"><span>SCIENCE CLASS</span></a></li>
        </ul>
    </div>
</section>

<!-- Class Dashboard -->
<div id="dashboard-cards">
    <a href="regGrade11.php" style="text-decoration: none; color: inherit;">
        <div class="card" style="background-color: #B19CD9 ;">
            <h5>Grade 11</h5>
            <div class="content-icon">
                <i class="fa fa-solid fa-list" style="border: 4px solid; padding:4px;"></i>
            </div>
        </div>
    </a>
    <a href="regGrade12.php" style="text-decoration: none; color: inherit;">
        <div class="card" style="background-color:rgb(21, 115, 152) ;">
            <h5>Grade 12</h5>
            <div class="content-icon">
                <i class="fa fa-solid fa-list" style="border: 4px solid; padding:4px;"></i>
            </div>
        </div>
    </a>
</div>
