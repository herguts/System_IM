<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Student</title>
  <link rel="stylesheet" href="adminhome.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
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

  <section id="add-student-content">
    <div class="container mt-4">
      <h2 class="mb-4">Add New Student</h2>
      <form id="studentForm" onsubmit="return handleFormSubmit(event)">
        <div class="form-group mb-3">
          <label for="lrn">LRN</label>
          <input type="text" class="form-control" id="lrn" name="lrn" required />
          <small class="form-text text-muted">Please enter exactly 12 digits for LRN.</small>
        </div>
        <div class="form-group mb-3">
          <label for="first_name">First Name</label>
          <input type="text" class="form-control" id="first_name" name="first_name" required />
        </div>
        <div class="form-group mb-3">
          <label for="middle_name">Middle Name</label>
          <input type="text" class="form-control" id="middle_name" name="middle_name" required />
        </div>
        <div class="form-group mb-3">
          <label for="last_name">Last Name</label>
          <input type="text" class="form-control" id="last_name" name="last_name" required />
        </div>
        <div class="form-group mb-3">
          <label for="phone_number">Phone Number</label>
          <input type="text" class="form-control" id="phone_number" name="phone_number" pattern="^09\d{9}$|^$" placeholder="If None Leave Blank" title="Phone number must be 11 digits starting with '09' or leave blank" />
        </div>
        <div class="form-group mb-3">
          <label for="email">Email Address</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="If None Leave Blank" pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" title="Invalid Email Address. Must end with @gmail.com" />
        </div>
        <div class="form-group mb-3">
          <label for="gender">Gender</label>
          <select class="form-control" id="gender" name="gender" required>
            <option value="" disabled selected>Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
        <div class="form-group mb-3">
          <label for="age">Age</label>
          <input type="text" class="form-control" id="age" name="age" />
        </div>
        <div class="form-group mb-3">
          <label for="grade">Grade</label>
          <select class="form-control" id="grade" name="grade" onchange="populateSections()" required>
            <option value="" disabled selected>Select Grade level</option>
            <option value="Grade 7">Grade 7</option>
            <option value="Grade 8">Grade 8</option>
            <option value="Grade 9">Grade 9</option>
            <option value="Grade 10">Grade 10</option>
            <option value="Grade 11">Grade 11</option>
            <option value="Grade 12">Grade 12</option>
          </select>
        </div>
        <div class="form-group mb-4">
          <label for="section">Section</label>
          <select class="form-control" id="section" name="section" required>
            <option value="" disabled selected>Select Section</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Student</button>
        <button type="button" class="btn btn-secondary ms-2" onclick="redirectToStudentList()">Cancel</button>
      </form>
    </div>
  </section>

  <!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">Success</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-success text-white">
        Student has been successfully added.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal" id="modalConfirmBtn">OK</button>
      </div>
    </div>
  </div>
</div>


  <script>
    const sectionsByGrade = {
      "Grade 7": ['Carrene', 'Katherine', 'Genalyn', 'Melanie', 'Reviena'],
      "Grade 8": ['Cherry', 'Judy Ann', 'Leo', 'Melisa'],
      "Grade 9": ['Edeth', 'Levah', 'Rea', 'Remelisa'],
      "Grade 10": ['Jacob', 'Kimberly', 'Maribeth', 'Mary Ju'],
      "Grade 11": ['Elin', 'Connie', 'Katheren', 'Venus', 'Marvin'],
      "Grade 12": ['Catheryn', 'Jacqueline', 'Jerred', 'Honey', 'Bethyl', 'Melmarie']
    };

    function populateSections() {
      const grade = document.getElementById('grade').value;
      const sectionSelect = document.getElementById('section');
      sectionSelect.innerHTML = '<option value="" disabled selected>Select Section</option>';
      if (sectionsByGrade[grade]) {
        sectionsByGrade[grade].forEach(section => {
          const option = document.createElement('option');
          option.value = section;
          option.textContent = section;
          sectionSelect.appendChild(option);
        });
      }
    }

    function redirectToStudentList() {
      window.location.href = 'studentlist.php';
    }

    function validateForm() {
      const phone = document.getElementById('phone_number').value.trim();
      const email = document.getElementById('email').value.trim();

      if (phone && !/^09\d{9}$/.test(phone)) {
        alert('Phone number must be 11 digits starting with 09 or leave blank.');
        return false;
      }

      if (email && !/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email)) {
        alert('Email must be a valid @gmail.com address or left blank.');
        return false;
      }

      return true;
    }

    function handleFormSubmit(event) {
      event.preventDefault();

      if (validateForm()) {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();

        document.getElementById('modalConfirmBtn').onclick = function () {
          document.getElementById('studentForm').reset();
          document.getElementById('section').innerHTML = '<option value="" disabled selected>Select Section</option>';
        };
      }

      return false;
    }
  </script>

  <!-- Bootstrap JS (required for modal) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>