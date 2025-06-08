<?php
// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust this path if your vendor/autoload.php is in a different location
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include your database connection file
    require 'connect.php';

    if (!$conn) {
        // Error on database connection
        header("Location: enroll.php?message=general_error&details=" . urlencode("Database connection failed."));
        exit();
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
            header("Location: enroll.php?message=general_error&details=" . urlencode("Missing required field: " . $field));
            exit();
        }
    }

    // Additional validation
    if (!in_array($status, ['new', 'returning', 'regular', 'transferee', 'als'])) {
        header("Location: enroll.php?message=general_error&details=" . urlencode("Invalid enrollment status."));
        exit();
    }

    if (!in_array($level, ['Junior', 'Senior'])) {
        header("Location: enroll.php?message=general_error&details=" . urlencode("Invalid level selection."));
        exit();
    }

    // Check for senior grade and strand if level is Senior
    if ($level == 'Senior') {
        if (empty($seniorGrade)) {
            header("Location: enroll.php?message=general_error&details=" . urlencode("Missing senior grade selection."));
            exit();
        }
        if (empty($strand)) {
            header("Location: enroll.php?message=general_error&details=" . urlencode("Missing strand selection."));
            exit();
        }
    }

    // Hardcoded for now — replace with session values in production
    $admin_id = 2; // Example admin ID

    // IMPORTANT: 'teach_no' is an INTEGER type and NOT NULL in the database.
    // To satisfy the NOT NULL constraint without touching the schema,
    // we must provide a non-NULL integer value.
    // Using 0 as a placeholder. If 'teach_no' is a FOREIGN KEY,
    // ensure a teacher record with ID 0 exists, or this will cause a foreign key constraint violation.
    $teach_no = null; // Set to 0 (or another placeholder integer like -1 or a specific 'unassigned' teacher ID)

    // Start a database transaction
    pg_query($conn, "BEGIN");

    try {
        // Check if LRN or email already exists to prevent duplicate entries
        $check_query = "SELECT stud_id FROM STUDENT WHERE stud_lrn = $1 OR stud_email = $2";
        $check_result = pg_query_params($conn, $check_query, [$lrn, $email]);
        if (pg_num_rows($check_result) > 0) {
            throw new Exception("A student with this LRN or email already exists.");
        }

        // Get type_id from ENROLLMENT_TYPE table based on selected status
        $type_result = pg_query_params($conn,
            "SELECT type_id FROM ENROLLMENT_TYPE WHERE type_name = $1",
            [$status]);
        $type_row = pg_fetch_assoc($type_result);
        if (!$type_row) {
            throw new Exception("Invalid enrollment type: " . htmlspecialchars($status));
        }
        $type_id = $type_row['type_id'];

        // Determine grade level name and strand ID based on selected level (Junior/Senior)
        $gradelvl_name = '';
        $strand_id = null;

        if ($level == 'Junior') {
            $gradelvl_name = $_POST['gradeStrand']; // For JHS, gradeStrand contains the grade level
        } else {
            // For SHS (Senior High School)
            $gradelvl_name = $seniorGrade;
            $strand_name = $strand;

            // Handle TVL specializations, appending to strand name if applicable
            if (str_contains($strand_name, 'TVL') && !empty($tvlSpecialization)) {
                $strand_name .= ' - ' . strtoupper($tvlSpecialization);
            }

            // Fetch strand_id from STRAND table
            $strand_result = pg_query_params($conn,
                "SELECT strand_id FROM STRAND WHERE strand_name = $1",
                [$strand_name]);
            $strand_row = pg_fetch_assoc($strand_result);
            $strand_id = $strand_row['strand_id'] ?? null;
            if (!$strand_id && $level == 'Senior') { // Only strict for SHS
                 throw new Exception("Invalid strand selection: " . htmlspecialchars($strand_name));
            }
        }

        // Get gradelvl_id from GRADE_LEVEL table
        $grade_result = pg_query_params($conn,
            "SELECT gradelvl_id FROM GRADE_LEVEL WHERE gradelvl_name = $1",
            [$gradelvl_name]);
        $grade_row = pg_fetch_assoc($grade_result);
        if (!$grade_row) {
            throw new Exception("Invalid grade level: " . htmlspecialchars($gradelvl_name));
        }
        $gradelvl_id = $grade_row['gradelvl_id'];

        // Initial student status is 'Pending'
        $initial_stud_status = 'Pending';

        // Insert new student record into STUDENT table
        $student_query = "INSERT INTO STUDENT
            (stud_lname, stud_fname, stud_midname, stud_email, stud_dob,
             stud_gender, stud_lrn, stud_phonenum, level, gradelvl_id, strand_id,
             type_id, teach_no, admin_id, stud_status)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15)
            RETURNING stud_id"; // RETURNING stud_id to get the newly inserted ID

        $student_params = [
            $lname, $fname, $mname, $email, $dob,
            $gender, $lrn, $phone, $level, $gradelvl_id, $strand_id,
            $type_id, $teach_no, $admin_id, $initial_stud_status
        ];

        $student_result = pg_query_params($conn, $student_query, $student_params);
        if (!$student_result) {
            throw new Exception("Student insertion failed: " . pg_last_error($conn));
        }
        $stud_id = pg_fetch_result($student_result, 0, 'stud_id'); // Get the newly created student ID

        // Define upload directory and create if it doesn't exist
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
            throw new Exception("Failed to create upload directory: " . $upload_dir);
        }

        // Function to handle file upload
        function handleUpload($file_key, $upload_dir) {
            if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] === UPLOAD_ERR_NO_FILE) {
                return null; // No file uploaded for this field
            }

            if ($_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File upload error for " . $file_key . ": Code " . $_FILES[$file_key]['error']);
            }

            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
            $file_type = $_FILES[$file_key]['type'];
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type for " . $file_key . ". Only JPEG, PNG, and PDF are allowed.");
            }

            // Max file size 5MB
            if ($_FILES[$file_key]['size'] > 5 * 1024 * 1024) {
                throw new Exception("File too large for " . $file_key . ". Maximum size is 5MB.");
            }

            $file_name = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES[$file_key]['name']));
            // Create a unique file name to prevent conflicts
            $target_path = $upload_dir . uniqid('upload_') . '_' . $file_name;

            if (!move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_path)) {
                throw new Exception("Failed to move uploaded file for " . $file_key . " to " . $target_path);
            }

            return $target_path; // Return the path where the file is saved
        }

        // Determine which files to process based on enrollment status
        $files_to_process = [];
        switch ($status) {
            case 'new':
            case 'transferee':
                $files_to_process = ['enrollFormUpload', 'goodMoralUpload', 'reportCardUpload', 'psaUpload'];
                break;
            case 'returning':
            case 'regular':
                $files_to_process = ['enrollFormUpload', 'reportCardUpload', 'psaUpload'];
                break;
            case 'als':
                $files_to_process = ['completionUpload', 'alsResultsUpload', 'psaUpload'];
                break;
        }

        // Prepare parameters for the REQUIREMENTS table
        $requirement_params = [
            $stud_id,
            null, // enrollFormUpload
            null, // goodMoralUpload
            null, // reportCardUpload
            null, // psaUpload
            null, // completionUpload
            null  // alsResultsUpload
        ];

        // Process file uploads and update requirement_params array
        foreach ($files_to_process as $file_key) {
            try {
                $file_path = handleUpload($file_key, $upload_dir);
                if ($file_path) {
                    switch ($file_key) {
                        case 'enrollFormUpload': $requirement_params[1] = $file_path; break;
                        case 'goodMoralUpload': $requirement_params[2] = $file_path; break;
                        case 'reportCardUpload': $requirement_params[3] = $file_path; break;
                        case 'psaUpload': $requirement_params[4] = $file_path; break;
                        case 'completionUpload': $requirement_params[5] = $file_path; break;
                        case 'alsResultsUpload': $requirement_params[6] = $file_path; break;
                    }
                }
            } catch (Exception $e) {
                // If a file upload fails, log the error and re-throw to trigger rollback
                error_log("File upload error: " . $e->getMessage());
                throw $e; // Re-throw to ensure transaction rollback
            }
        }

        // Insert into REQUIREMENTS table
        $requirement_query = "INSERT INTO requirements
            (stud_id, enrollFormUpload, goodMoralUpload, reportCardUpload, psaUpload, completionUpload, alsResultsUpload)
            VALUES ($1, $2, $3, $4, $5, $6, $7)";

        $req_result = pg_query_params($conn, $requirement_query, $requirement_params);
        if (!$req_result) {
            throw new Exception("Requirement insertion failed: " . pg_last_error($conn));
        }

        // Commit the transaction if all operations were successful
        pg_query($conn, "COMMIT");

        // --- Email Sending Logic for Pending Status using PHPMailer ---
        $student_full_name = htmlspecialchars($fname . " " . $lname);

        $mail = new PHPMailer(true); // Enable exceptions

        try {
            // Server settings (from your provided test code)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'henryjamesmadrid14@gmail.com'; // YOUR Gmail address here
            $mail->Password   = 'bviq bfff uhsm jiwh';       // YOUR Gmail app password here
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8'; // Ensure proper character encoding

            // Recipients
            $mail->setFrom('joneljumawan16@gmail.com', 'Magdugo NHS Enrollment'); // Sender's email and name
            $mail->addAddress($email, $student_full_name); // Student's email and name

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Enrollment Status - Magdugo National Highschool';
            // HTML content for the 'Pending' email
            $mail->Body = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Status - Magdugo National Highschool</title>
    <style>
        /* Basic Reset & Body Styles */
        body {
            font-family: 'Inter', sans-serif; /* Fallback to sans-serif */
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* Container for the email content */
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        /* Header Styles */
        .email-header {
            background-color: #dc2626; /* Deep Red */
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .email-header p {
            margin: 5px 0 0;
            font-size: 16px;
            font-weight: 400;
        }

        /* Body Content Styles */
        .email-body {
            padding: 30px;
            font-size: 15px;
            line-height: 1.8;
            color: #555;
        }
        .email-body p {
            margin-bottom: 15px;
        }
        .email-body strong {
            /* This will be overridden by inline style for status */
            color: #dc2626; /* Deep Red for emphasis */
        }

        /* Footer Styles */
        .email-footer {
            background-color: #f8f8f8;
            color: #777;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            border-top: 1px solid #eee;
        }
        .email-footer a {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
        }

        /* Responsive Styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0;
                border-radius: 0;
            }
            .email-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Magdugo National Highschool</h1>
            <p>Enrollment Status Update</p>
        </div>
        <div class="email-body">
            <p>Dear <strong>{$student_full_name}</strong>,</p>

            <p>Thank you for enrolling at Magdugo National Highschool. Your enrollment application has been successfully submitted.</p>

            <p>Your enrollment status is currently <strong style="color: #dc2626;">Pending</strong>. We will review your application and documents.</p>

            <p>You will receive another email once your enrollment status has been updated.</p>

            <p>For any inquiries, please contact the school administration.</p>

            <p>Sincerely,</p>
            <p>Magdugo National Highschool Administration</p>
        </div>
        <div class="email-footer">
            <p>&copy; 2024 Magdugo National Highschool. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
            <p>Visit our website: <a href="http://your-school-website.com" target="_blank">your-school-website.com</a></p>
        </div>
    </div>
</body>
</html>
EOT;

            $mail->send();
            error_log("PHPMailer: Enrollment pending confirmation email sent to: " . $email);
            // Redirect with success message
            header("Location: enroll.php?message=success");
            exit(); // Crucial to prevent further output before redirect
        } catch (Exception $e) {
            error_log("PHPMailer Error sending email to " . $email . ": " . $mail->ErrorInfo);
            // Redirect with email sending error message
            header("Location: enroll.php?message=email_error&details=" . urlencode($e->getMessage()));
            exit(); // Crucial
        }
        // --- End Email Sending Logic ---

    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        pg_query($conn, "ROLLBACK");
        error_log("Enrollment Error: " . $e->getMessage()); // Log the error on the server side
        // Redirect with general error message
        header("Location: enroll.php?message=general_error&details=" . urlencode($e->getMessage()));
        exit(); // Crucial
    } finally {
        // Close the database connection
        pg_close($conn);
    }
    exit(); // Terminate script execution after processing POST request
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Enroll - Magdugo NHS</title>
  <!-- Link to your external stylesheet -->
  <link rel="stylesheet" href="style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Add basic styling if style.css is not robust enough or for quick adjustments */
    body {
        font-family: 'Inter', sans-serif;
    }
    .name-group {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .name-field {
        flex: 1 1 calc(33% - 20px); /* Adjusts for 3 columns on larger screens */
        min-width: 250px; /* Ensures fields don't get too small */
    }

    /* Custom Modal Styles */
    .modal {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 1000; /* Sit on top */
      left: 0;
      top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: rgba(0,0,0,0.6); /* Darker overlay */
      display: flex;
      justify-content: center;
      align-items: center;
      transition: opacity 0.3s ease;
      opacity: 0; /* Start hidden for transition */
      pointer-events: none; /* Allow clicks through when hidden */
    }
    .modal.show {
        opacity: 1;
        pointer-events: auto;
    }
    .modal-content {
      background-color: #ffffff;
      margin: auto;
      padding: 30px;
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2); /* Stronger shadow */
      max-width: 450px; /* Slightly wider */
      width: 90%; /* Responsive width */
      text-align: center;
      transform: translateY(-20px); /* Start slightly above for subtle animation */
      transition: transform 0.3s ease;
    }
    .modal.show .modal-content {
        transform: translateY(0); /* Move to final position */
    }
    .modal-content h3 {
      font-size: 1.8rem; /* Larger title */
      font-weight: bold;
      color: #dc2626; /* Default red for consistent branding */
      margin-bottom: 1rem;
    }
    .modal-content p {
        font-size: 1.1rem;
        color: #555;
        margin-bottom: 1.5rem;
    }
    .modal-content button {
      background-color: #dc2626; /* Deep red */
      color: white;
      font-weight: bold;
      padding: 0.75rem 2rem; /* Larger button */
      border-radius: 0.75rem; /* More rounded */
      transition: background-color 0.3s ease, transform 0.2s ease;
      cursor: pointer;
      border: none;
    }
    .modal-content button:hover {
      background-color: #b91c1c; /* Darker red on hover */
      transform: translateY(-2px); /* Subtle lift effect */
    }

    /* Success/Error text colors for modal title */
    .modal-content h3.success-color {
        color: #16a34a; /* Green */
    }
    .modal-content h3.error-color {
        color: #dc2626; /* Red */
    }

    .file-preview {
        margin-top: 8px;
        padding: 8px;
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        background-color: #f9f9f9;
        font-size: 0.9em;
        color: #666;
    }
    .file-preview img, .file-preview embed {
        max-width: 100%;
        height: auto;
        margin-top: 8px;
        border-radius: 0.5rem;
    }
  </style>
</head>
<body>
  <header>
    <div class="head">
      <img src="images/header.png" alt="Website Header">
    </div>

    <div class="header-logos">
      <div class="logo-group left">
        <div class="logo"><img src="images/logo1.png" alt="Logo 1" /></div>
        <div class="logo">
            <a href="studentRegister/mainStudent/admin_login.php" class="logo-link">
                <img src="images/logo2.png" alt="Logo 2" />
            </a>
        </div>
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
      <a href="enroll.php" class="active">Enroll</a>
    </nav>
  </header>

  <main>
    <section class="enroll-form">
      <h2>ENROLL NOW</h2>
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

        <div id="uploadFields"></div>

        <button type="submit">SUBMIT</button>
      </form>
    </section>
  </main>

  <!-- Custom Modal for Messages -->
  <div id="customModal" class="modal hidden">
    <div class="modal-content">
      <h3 id="modalTitle"></h3>
      <p id="modalMessage"></p>
      <button id="closeCustomModal">OK</button>
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

    // New custom modal elements
    const customModal = document.getElementById("customModal");
    const closeCustomModalBtn = document.getElementById("closeCustomModal");
    const modalTitle = document.getElementById("modalTitle");
    const modalMessage = document.getElementById("modalMessage");

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

      // Reset values when changing level
      if (selected === "Junior") {
          document.getElementById("seniorGrade").value = "";
          document.getElementById("strand").value = "";
          tvlSpecialization.innerHTML = '<option value="">-- Choose Specialization --</option>';
      } else if (selected === "Senior") {
          document.getElementById("gradeStrand").value = "";
      }
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

    // Dynamic Upload Fields
    const statusSelect = document.getElementById("status");
    const uploadFieldsContainer = document.getElementById("uploadFields");

    const uploadTemplates = {
      new: [
        { id: "enrollFormUpload", label: "Accomplished Enrollment Form", required: true },
        { id: "goodMoralUpload", label: "Good Moral Certificate", required: true },
        { id: "reportCardUpload", label: "School Form 9 (Report Card)", required: true },
        { id: "psaUpload", label: "PSA Birth Certificate", required: true }
      ],
      returning: [
        { id: "enrollFormUpload", label: "Accomplished Enrollment Form", required: true },
        { id: "reportCardUpload", label: "School Form 9 (Report Card)", required: true },
        { id: "psaUpload", label: "PSA Birth Certificate", required: true }
      ],
      regular: [
        { id: "enrollFormUpload", label: "Accomplished Enrollment Form", required: true },
        { id: "reportCardUpload", label: "School Form 9 (Report Card)", required: true },
        { id: "psaUpload", label: "PSA Birth Certificate", required: true }
      ],
      transferee: [
        { id: "enrollFormUpload", label: "Accomplished Enrollment Form", required: true },
        { id: "goodMoralUpload", label: "Good Moral Certificate", required: true },
        { id: "reportCardUpload", label: "School Form 9 (Report Card)", required: true },
        { id: "psaUpload", label: "PSA Birth Certificate", required: true }
      ],
      als: [
        { id: "completionUpload", label: "Certification of Completion/Diploma", required: true },
        { id: "alsResultsUpload", label: "ALS Results", required: true },
        { id: "psaUpload", label: "PSA Birth Certificate", required: true }
      ]
    };

    function renderUploadFields(status) {
      uploadFieldsContainer.innerHTML = ""; // Clear existing fields
      if (!uploadTemplates[status]) return;

      uploadTemplates[status].forEach(field => {
        const container = document.createElement("div");
        container.className = "upload-field";

        const label = document.createElement("label");
        label.setAttribute("for", field.id);
        label.textContent = field.label + (field.required ? " *" : "");

        const input = document.createElement("input");
        input.type = "file";
        input.id = field.id;
        input.name = field.id;
        input.accept = "image/*,.pdf"; // Only images and PDFs allowed
        input.required = field.required;

        const previewDiv = document.createElement("div");
        previewDiv.className = "file-preview";
        previewDiv.id = field.id + "Preview";
        previewDiv.innerHTML = "<p>No file chosen</p>";

        // Event listener for file input to display preview
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    if (file.type.startsWith('image')) {
                        previewDiv.innerHTML = `<img src="${e.target.result}" alt="Image Preview">`;
                    } else if (file.type === 'application/pdf') {
                        previewDiv.innerHTML = `<embed src="${e.target.result}" type="application/pdf" width="100%" height="200px" />`;
                    } else {
                        previewDiv.innerHTML = `<p>File chosen: ${file.name} (${(file.size / 1024).toFixed(2)} KB)</p>`;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                previewDiv.innerHTML = "<p>No file chosen</p>";
            }
        });

        container.appendChild(label);
        container.appendChild(input);
        container.appendChild(previewDiv);
        uploadFieldsContainer.appendChild(container);
      });
    }

    statusSelect.addEventListener("change", function() {
      renderUploadFields(this.value);
    });

    // Function to show the custom modal
    function showCustomModal(title, message, isError = false) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        // Apply color based on success/error
        modalTitle.classList.remove('success-color', 'error-color'); // Clear previous classes
        if (isError) {
            modalTitle.classList.add('error-color');
        } else {
            modalTitle.classList.add('success-color');
        }
        customModal.classList.add('show'); // Use 'show' class for transition
    }

    // Close custom modal event listener
    closeCustomModalBtn.addEventListener('click', function() {
        customModal.classList.remove('show');
        // No need to redirect here, PHP already handled it
    });

    // Initialize upload fields and dynamic containers on page load
    document.addEventListener("DOMContentLoaded", function() {
      renderUploadFields(statusSelect.value);
      levelSelect.dispatchEvent(new Event('change')); // Trigger change to set initial display for grade/strand

      // Check URL parameters for messages and display custom modal
      const urlParams = new URLSearchParams(window.location.search);
      const messageType = urlParams.get('message');
      const messageDetails = urlParams.get('details'); // Decoded automatically by URLSearchParams

      if (messageType) {
          if (messageType === 'success') {
              showCustomModal('Enrollment Application Submitted', 'Check your enrollment status on your email.', false);
          } else if (messageType === 'email_error') {
              showCustomModal('Email Sending Failed', `Enrollment submitted, but we could not send the confirmation email. Please check server logs. Details: ${messageDetails || 'N/A'}`, true);
          } else if (messageType === 'general_error') {
              showCustomModal('Enrollment Error', `An error occurred: ${messageDetails || 'Please try again later.'}`, true);
          }
          // Clear URL parameters to prevent modal re-showing on refresh
          history.replaceState({}, document.title, "enroll.php");
      }
    });

    // Handle dummy client-side form submission to show modal (actual submission handled by PHP)
    form.addEventListener("submit", function(e) {
      // Client-side validation could be added here before PHP submission.
      // This is primarily handled by server-side PHP for this setup,
      // which then redirects back with a message.
    });

  </script>
</body>
</html>
