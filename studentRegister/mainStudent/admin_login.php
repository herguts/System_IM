<?php
session_start();

require_once '../../connect.php';

// Verify database connection
if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Input validation
    if (empty($username) || empty($password)) {
        $error = "Both username and password are required.";
    } else {
        $query = "SELECT admin_id, admin_user, admin_pass FROM ADMIN WHERE admin_user = $1";
        $result = pg_query_params($conn, $query, [$username]);

        if ($result && pg_num_rows($result) === 1) {
            $row = pg_fetch_assoc($result);
            
            // Verify password (works for both plaintext and hashed during transition)
            if (password_verify($password, $row['admin_pass']) || $password === $row['admin_pass']) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                
                // Store minimal admin info in session
                $_SESSION['admin_id'] = $row['admin_id'];
                $_SESSION['admin_user'] = $row['admin_user'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to admin home
                header("Location: adminhome.php");
                exit;
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login - Magdugo NHS</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-container {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }
    h2 {
      color: #b10000;
      text-align: center;
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }
    input {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }
    button {
      background-color: #b10000;
      color: white;
      border: none;
      padding: 0.75rem;
      width: 100%;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s;
    }
    button:hover {
      background-color: #8a0000;
    }
    .error {
      color: #dc3545;
      text-align: center;
      margin-top: 1rem;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>
    <form method="POST" action="admin_login.php">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required autocomplete="username">
      
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
      
      <button type="submit">Login</button>
      <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </form>
  </div>
</body>
</html>