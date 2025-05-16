<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'connect.php';

    if (!$conn) {
        die("Database connection failed.");
    }

    // Collect all form data
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $level = $_POST['level'];
    $senior_grade = $_POST['seniorGrade'] ?? null;
    $grade_strand = $_POST['gradeStrand'];
    $school_id = $_POST['school_id'];

    // Handle specialization fields (optional)
    $specialization_11 = $_POST['grade11Tvl'] ?? null;
    $specialization_12 = $_POST['grade12Tvl'] ?? null;

    
    $target_dir = "uploads/";
    // File uploads
    $psaFile = $_FILES['psaUpload'];
    $gradeSlipFile = $_FILES['gradeSlipUpload'];

    $psaContent = pg_escape_bytea($conn, file_get_contents($psaFile['tmp_name']));
    $gradeSlipContent = pg_escape_bytea($conn, file_get_contents($gradeSlipFile['tmp_name']));
    

    // Prepare and execute query
    $query = "INSERT INTO enrollments 
    (full_name, email, dob, gender, phone, level, senior_grade, grade_strand, specialization_11, specialization_12, school_id, psa_file, grade_slip_file) 
    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)";

    $result = pg_query_params($conn, $query, [
        $full_name, $email, $dob, $gender, $phone, $level,
        $senior_grade, $grade_strand, $specialization_11, $specialization_12,
        $school_id, $psaContent, $gradeSlipContent
    ]);

    if ($result) {
        echo "<script>
            alert('Enrollment successfully submitted and injected to the enrollments table.');
            window.location.href = 'enroll.php'; // Redirect to refresh the form
        </script>";
    } else {
        echo "<script>
            alert('Error occurred: " . pg_last_error($conn) . "');
            window.history.back();
        </script>";
    }
    
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Enroll - Magdugo NHS</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header>
    <div class="head">
      <img src="images/header.png" alt="Website Header">
    </div>

    <div class="header-logos">
      <div class="logo-group left">
        <div class="logo"><img src="images/logo1.png" alt="Logo 1" /></div>
        <div class="logo"><img src="images/logo2.png" alt="Logo 2" /></div>
      </div>

      <div class="school-title">
        <h1>MAGDUGO<br><span>NATIONAL HIGHSCHOOL</span></h1>
      </div>

      <div class="logo-group right">
        <div class="logo"><img src="images/logo3.png" alt="Logo 3" /></div>
        <div class="logo"><img src="images/logo4.png" alt="Logo 4" /></div>
      </div>
    </div>

    <nav>
      <a href="home.php">Home</a>
      <a href="about.php">About</a>
      <a href="contact.php">Contact</a>
      <a href="enroll.php" class="active" >Enroll</a>
    </nav>
  </header>
  
  <main>
    <section class="enroll-form">
      <h2>ENROLL NOW</h2>
      <form action="enroll.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>

        <input type="date" name="dob" placeholder="Date of Birth" required>

        <label for="gender">Gender:</label>
        <select name="gender" id="gender" required>
          <option value="">-- Select Gender --</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>

        <input type="tel" name="phone" placeholder="Phone Number" pattern="[0-9]{11}" required>

        <label for="level">Select Level:</label>
        <select id="level" name="level" required>
          <option value="">-- Choose Level --</option>
          <option value="junior">Junior High School</option>
          <option value="senior">Senior High School</option>
        </select>

        <div id="seniorGradeContainer" style="display: none;">
          <label for="seniorGrade">Select Grade:</label>
          <select id="seniorGrade" name="seniorGrade">
            <option value="">-- Choose Grade --</option>
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
          </select>
        </div>

        <label for="gradeStrand">Grade / Strand:</label>
        <select id="gradeStrand" name="gradeStrand" required>
          <option value="">-- Select Grade or Strand --</option>
        </select>

        <div id="grade11Options" style="display: none;">
          <label for="grade11Tvl">Choose Specialization (Grade 11):</label>
          <select id="grade11Tvl" name="grade11Tvl">
            <option value="">-- Choose Specialization --</option>
            <option value="dressmaking">Dress Making</option>
            <option value="cookery">Cookery</option>
          </select>
        </div>

        <div id="grade12Options" style="display: none;">
          <label for="grade12Tvl">Choose Specialization (Grade 12):</label>
          <select id="grade12Tvl" name="grade12Tvl">
            <option value="">-- Choose Specialization --</option>
            <option value="tailoring">Tailoring</option>
            <option value="bpp">BPP</option>
            <option value="hilot">Hilot Wellness</option>
          </select>
        </div>

        <input type="text" name="school_id" placeholder="School ID" required>

        <label for="psaUpload">Upload PSA Birth Certificate:</label>
        <input type="file" id="psaUpload" name="psaUpload" accept="image/*,application/pdf" required>
        <div class="chosen-files" id="psaChosenFilesContainer"><p>No files selected</p></div>

        <label for="gradeSlipUpload">Upload Grade Slip:</label>
        <input type="file" id="gradeSlipUpload" name="gradeSlipUpload" accept="image/*,application/pdf" required>
        <div class="chosen-files" id="gradeSlipChosenFilesContainer"><p>No files selected</p></div>

        <button type="submit">SUBMIT</button>
      </form>
    </section>
  </main>

  <div id="successModal">
    <div class="modal-content">
      <h3>Enrollment Successfully Submitted</h3>
      <button id="closeModal">OK</button>
    </div>
  </div>

  <script>
    const levelSelect = document.getElementById("level");
    const seniorGradeContainer = document.getElementById("seniorGradeContainer");
    const seniorGrade = document.getElementById("seniorGrade");
    const gradeStrandSelect = document.getElementById("gradeStrand");
    const grade11Options = document.getElementById("grade11Options");
    const grade12Options = document.getElementById("grade12Options");

    const psaFileInput = document.getElementById("psaUpload");
    const gradeSlipFileInput = document.getElementById("gradeSlipUpload");
    const psaChosenFilesContainer = document.getElementById("psaChosenFilesContainer");
    const gradeSlipChosenFilesContainer = document.getElementById("gradeSlipChosenFilesContainer");
    const form = document.getElementById("enrollForm");
    const modal = document.getElementById("successModal");
    const closeModalBtn = document.getElementById("closeModal");

    const options = {
      junior: ["Grade 7", "Grade 8", "Grade 9", "Grade 10"],
      senior: [
        "STEM", "HUMSS", "TVL - Carpentry", "TVL - SMAW", "TVL - CSS", "TVL - EIM",
        "TVL - Home Economics"
      ]
    };

    levelSelect.addEventListener("change", function () {
      const selected = this.value;
      seniorGradeContainer.style.display = selected === "senior" ? "block" : "none";
      gradeStrandSelect.innerHTML = '<option value="">-- Select Grade / Strand --</option>';
      if (options[selected]) {
        options[selected].forEach(item => {
          const opt = document.createElement("option");
          opt.value = item;
          opt.textContent = item;
          gradeStrandSelect.appendChild(opt);
        });
      }
      grade11Options.style.display = "none";
      grade12Options.style.display = "none";
    });

    gradeStrandSelect.addEventListener("change", function () {
      const selectedStrand = this.value;
      const selectedGrade = seniorGrade.value;

      if (selectedStrand === "TVL - Home Economics") {
        if (selectedGrade === "11") {
          grade11Options.style.display = "block";
          grade12Options.style.display = "none";
        } else if (selectedGrade === "12") {
          grade11Options.style.display = "none";
          grade12Options.style.display = "block";
        }
      } else {
        grade11Options.style.display = "none";
        grade12Options.style.display = "none";
      }
    });

    seniorGrade.addEventListener("change", function () {
      const selectedStrand = gradeStrandSelect.value;
      const selectedGrade = this.value;
      if (selectedStrand === "TVL - Home Economics") {
        if (selectedGrade === "11") {
          grade11Options.style.display = "block";
          grade12Options.style.display = "none";
        } else if (selectedGrade === "12") {
          grade11Options.style.display = "none";
          grade12Options.style.display = "block";
        }
      } else {
        grade11Options.style.display = "none";
        grade12Options.style.display = "none";
      }
    });

    function createFileListItem(file, containerId) {
      const li = document.createElement("li");
      li.textContent = file.name;

      const contentDiv = document.createElement("div");
      contentDiv.classList.add("file-content");

      const reader = new FileReader();
      reader.onload = function (e) {
        const content = e.target.result;
        if (file.type.startsWith("image/")) {
          const img = document.createElement("img");
          img.src = content;
          img.style.maxWidth = "100%";
          img.style.maxHeight = "400px";
          contentDiv.appendChild(img);
        } else if (file.type === "application/pdf") {
          const iframe = document.createElement("iframe");
          iframe.src = content;
          iframe.style.width = "100%";
          iframe.style.height = "400px";
          contentDiv.appendChild(iframe);
        }
      };
      reader.readAsDataURL(file);

      li.appendChild(contentDiv);
      let isVisible = false;
      li.addEventListener("click", function () {
        isVisible = !isVisible;
        contentDiv.style.display = isVisible ? "block" : "none";
      });

      const ul = document.createElement("ul");
      ul.appendChild(li);

      const container = document.getElementById(containerId);
      container.innerHTML = "";
      container.appendChild(ul);
    }

    psaFileInput.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        createFileListItem(file, "psaChosenFilesContainer");
      } else {
        psaChosenFilesContainer.innerHTML = "<p>No files selected</p>";
      }
    });

    gradeSlipFileInput.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        createFileListItem(file, "gradeSlipChosenFilesContainer");
      } else {
        gradeSlipChosenFilesContainer.innerHTML = "<p>No files selected</p>";
      }
    });

   

  </script>
</body>
</html>