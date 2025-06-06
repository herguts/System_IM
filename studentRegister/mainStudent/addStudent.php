<?php
// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../../connect.php';

    if (!$conn) {
        die("Database connection failed: " . pg_last_error());
    }

    // Collect and validate form data
    $lname = trim($_POST['lastName'] ?? '');
    $fname = trim($_POST['firstName'] ?? '');
    $mname = trim($_POST['middleName'] ?? null);
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $dob = $_POST['dob'] ?? '';
    $gender = ucfirst(strtolower($_POST['gender'] ?? ''));
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $lrn = trim($_POST['lrn'] ?? '');
    $level = ($_POST['level'] == 'Senior') ? 'Senior' : 'Junior';
    $status = $_POST['status'] ?? '';
    $seniorGrade = $_POST['seniorGrade'] ?? '';
    $strand = $_POST['strand'] ?? '';
    $tvlSpecialization = $_POST['tvlSpecialization'] ?? null;

    // Validate required fields
    $required = ['lastName', 'firstName', 'email', 'dob', 'gender', 'phone', 'lrn', 'level', 'status'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("<script>alert('Missing required field: $field'); window.history.back();</script>");
        }
    }

    // Additional validation
    if (!in_array($status, ['new', 'returning', 'regular', 'transferee', 'als'])) {
        die("<script>alert('Invalid enrollment status'); window.history.back();</script>");
    }

    if (!in_array($level, ['Junior', 'Senior'])) {
        die("<script>alert('Invalid level'); window.history.back();</script>");
    }

    if ($level == 'Senior' && empty($seniorGrade)) {
        die("<script>alert('Missing senior grade selection'); window.history.back();</script>");
    }

    if ($level == 'Senior' && empty($strand)) {
        die("<script>alert('Missing strand selection'); window.history.back();</script>");
    }

    // Hardcoded for now — replace with session values in production
    $admin_id = 2;
    $teach_no = null;

    pg_query($conn, "BEGIN");

    try {
        // Check if LRN or email already exists
        $check_query = "SELECT stud_id FROM STUDENT WHERE stud_lrn = $1 OR stud_email = $2";
        $check_result = pg_query_params($conn, $check_query, [$lrn, $email]);
        if (pg_num_rows($check_result) > 0) {
            throw new Exception("A student with this LRN or email already exists");
        }

        // Get type_id
        $type_result = pg_query_params($conn, 
            "SELECT type_id FROM ENROLLMENT_TYPE WHERE type_name = $1", 
            [$status]);
        $type_row = pg_fetch_assoc($type_result);
        if (!$type_row) throw new Exception("Invalid enrollment type: $status");
        $type_id = $type_row['type_id'];

        // Get grade level ID based on selection
        $gradelvl_name = '';
        $strand_id = null;

        if ($level == 'Junior') {
            $gradelvl_name = $_POST['gradeStrand']; // For JHS, gradeStrand contains the grade level
        } else {
            // For SHS
            $gradelvl_name = $seniorGrade;
            $strand_name = $strand;
            
            // Handle TVL specializations
            if (str_contains($strand_name, 'TVL') && !empty($tvlSpecialization)) {
                $strand_name .= ' - ' . strtoupper($tvlSpecialization);
            }
            
            $strand_result = pg_query_params($conn,
                "SELECT strand_id FROM STRAND WHERE strand_name = $1",
                [$strand_name]);
            $strand_row = pg_fetch_assoc($strand_result);
            $strand_id = $strand_row['strand_id'] ?? null;
        }

        // Get grade level ID
        $grade_result = pg_query_params($conn,
            "SELECT gradelvl_id FROM GRADE_LEVEL WHERE gradelvl_name = $1",
            [$gradelvl_name]);
        $grade_row = pg_fetch_assoc($grade_result);
        if (!$grade_row) throw new Exception("Invalid grade level: $gradelvl_name");
        $gradelvl_id = $grade_row['gradelvl_id'];

        // Insert into STUDENT
        $student_query = "INSERT INTO STUDENT 
            (stud_lname, stud_fname, stud_midname, stud_email, stud_dob, 
             stud_gender, stud_lrn, stud_phonenum, level, gradelvl_id, strand_id,
             type_id, teach_no, admin_id)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)
            RETURNING stud_id";

        $student_params = [
            $lname, $fname, $mname, $email, $dob,
            $gender, $lrn, $phone, $level, $gradelvl_id, $strand_id,
            $type_id, $teach_no, $admin_id
        ];

        $student_result = pg_query_params($conn, $student_query, $student_params);
        if (!$student_result) throw new Exception("Student insertion failed: " . pg_last_error($conn));
        $stud_id = pg_fetch_result($student_result, 0, 'stud_id');

        pg_query($conn, "COMMIT");

        echo "<script>
            alert('Enrollment submitted successfully. Student ID: $stud_id');
            window.location.href = 'studentList.php';
        </script>";
    } catch (Exception $e) {
        pg_query($conn, "ROLLBACK");
        error_log("Enrollment Error: " . $e->getMessage());
        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    }

    pg_close($conn);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Add Student</title>
  <link rel="stylesheet" href="../../style.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
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
        <button class="db-btn" type="button" onclick="history.back()"><i class="fas fa-times"></i> Back</button>
    </form>
</div>

  <main>
    <section class="enroll-form">
      <h2>Add Student</h2>
      <form id="enrollForm" method="POST" enctype="multipart/form-data">
     

    <div class="name-group">
            <div class="name-field">
              <label for="lname">Last Name:</label> <br><br>
              <input type="text" id="lname" name="lastName" placeholder="Enter Last Name" required>
            </div>
            
            <div class="name-field">
              <br><label for="fname">First Name:</label><br><br>
              <input type="text" id="fname" name="firstName" placeholder="Enter First Name" required>
            </div>
            
            <div class="name-field">
              <br><label for="mname">Middle Name:</label><br><br>
              <input type="text" id="mname" name="middleName" placeholder="Enter Middle Name">
            </div>
          </div>
          
          <label for="email">Email Address:</label>
          <input type="email" id="email" name="email" placeholder="Enter Email Address" autocomplete="email" required>
          <label for="dob">Date Of Birth:</label>
          <input type="date" id="dob" name="dob" placeholder="Date of Birth" required>

          <label for="gender">Gender:</label>
          <select name="gender" id="gender" required>
            <option value="">-- Select Gender --</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>

          <label for="lrn">LRN:</label>
          <input type="text" id="lrn" name="lrn" placeholder="Enter LRN" autocomplete="off" required>

          <label for="phone">Phone Number:</label>
          <input type="tel" id="phone" name="phone" placeholder="Enter Phone Number" pattern="[0-9]{11}" autocomplete="tel" required>

          <label for="level">Select Level:</label>
          <select id="level" name="level" required>
            <option value="">-- Choose Level --</option>
            <option value="Junior">Junior High School</option>
            <option value="Senior">Senior High School</option>
          </select>

          <div id="seniorGradeContainer" style="display: none;">
            <label for="seniorGrade">Select Grade:</label>
            <select id="seniorGrade" name="seniorGrade">
              <option value="">-- Choose Grade --</option>
              <option value="Grade 11">Grade 11</option>
              <option value="Grade 12">Grade 12</option>
            </select>
          </div>

          <div id="juniorGradeContainer">
            <label for="gradeStrand">Grade Level:</label>
            <select id="gradeStrand" name="gradeStrand">
              <option value="">-- Select Grade --</option>
              <option value="Grade 7">Grade 7</option>
              <option value="Grade 8">Grade 8</option>
              <option value="Grade 9">Grade 9</option>
              <option value="Grade 10">Grade 10</option>
            </select>
          </div>

          <div id="strandContainer" style="display: none;">
            <label for="strand">Strand:</label>
            <select id="strand" name="strand">
              <option value="">-- Select Strand --</option>
              <option value="STEM">STEM</option>
              <option value="HUMSS">HUMSS</option>
              <option value="TVL - Carpentry">TVL - Carpentry</option>
              <option value="TVL - SMAW">TVL - SMAW</option>
              <option value="TVL - CSS">TVL - CSS</option>
              <option value="TVL - EIM">TVL - EIM</option>
              <option value="TVL - Home Economics">TVL - Home Economics</option>
              <option value="TVL - COOKERY">TVL - COOKERY</option>
              <option value="TVL - DRESSMAKING">TVL - DRESSMAKING</option>
              <option value="TVL - WELLNESS MASSAGE">TVL - WELLNESS MASSAGE</option>
              <option value="TVL - BPP">TVL - BPP</option>
            </select>
          </div>

          <div id="tvlSpecializationContainer" style="display: none;">
            <label for="tvlSpecialization">TVL Specialization:</label>
            <select id="tvlSpecialization" name="tvlSpecialization">
              <option value="">-- Choose Specialization --</option>
              <!-- Will be populated by JavaScript -->
            </select>
          </div>

          <label for="status">Status:</label>
          <select id="status" name="status" required>
            <option value="">-- Select Status --</option>
            <option value="new">New Student</option>
            <option value="returning">Returning Student</option>
            <option value="regular">Regular Enrollee</option>
            <option value="transferee">Transferee</option>
            <option value="als">ALS</option>
          </select>

          <button type="submit">SUBMIT</button>
        </form>
      </section>
  </main>

  <div id="successModal" class="modal">
    <div class="modal-content">
      <h3>Enrollment Successfully Submitted</h3>
      <button id="closeModal">OK</button>
    </div>
  </div>

  <script>
    const levelSelect = document.getElementById("level");
    const seniorGradeContainer = document.getElementById("seniorGradeContainer");
    const juniorGradeContainer = document.getElementById("juniorGradeContainer");
    const strandContainer = document.getElementById("strandContainer");
    const gradeStrandSelect = document.getElementById("gradeStrand");
    const strandSelect = document.getElementById("strand");
    const tvlSpecializationContainer = document.getElementById("tvlSpecializationContainer");
    const tvlSpecialization = document.getElementById("tvlSpecialization");

    const form = document.getElementById("enrollForm");
    const modal = document.getElementById("successModal");
    const closeModalBtn = document.getElementById("closeModal");

    // TVL specializations mapping
    const tvlSpecializations = {
      "TVL - Home Economics": ["Cookery", "Dressmaking"],
      "TVL - COOKERY": ["Commercial Cooking"],
      "TVL - DRESSMAKING": ["Dressmaking"],
      "TVL - WELLNESS MASSAGE": ["Hilot Wellness"],
      "TVL - BPP": ["Bread and Pastry Production"]
    };

    // Update form based on level selection
    levelSelect.addEventListener("change", function() {
      const selected = this.value;
      seniorGradeContainer.style.display = selected === "Senior" ? "block" : "none";
      juniorGradeContainer.style.display = selected === "Junior" ? "block" : "none";
      strandContainer.style.display = selected === "Senior" ? "block" : "none";
      tvlSpecializationContainer.style.display = "none";
      
      // Clear TVL specialization
      tvlSpecialization.innerHTML = '<option value="">-- Choose Specialization --</option>';
    });

    // Show TVL specialization if a TVL strand is selected
    strandSelect.addEventListener("change", function() {
      const selectedStrand = this.value;
      const isTVL = selectedStrand.includes("TVL");
      
      tvlSpecializationContainer.style.display = isTVL ? "block" : "none";
      tvlSpecialization.innerHTML = '<option value="">-- Choose Specialization --</option>';
      
      if (isTVL && tvlSpecializations[selectedStrand]) {
        tvlSpecializations[selectedStrand].forEach(spec => {
          const opt = document.createElement("option");
          opt.value = spec;
          opt.textContent = spec;
          tvlSpecialization.appendChild(opt);
        });
      }
    });
  </script>

</body>
</html>
