<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - ABIT</title>
    <link rel="icon" href="../img/abit-logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center">Initial Admin Setup</h2>
                <?php
                require_once __DIR__ . '/../../config/config.php';

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $username = trim($_POST['username']);
                    $password = trim($_POST['password']);
                    $confirmPassword = trim($_POST['confirm_password']);

                    if (empty($username) || empty($password) || empty($confirmPassword)) {
                        echo '<div class="alert alert-danger">All fields are required.</div>';
                    } elseif ($password !== $confirmPassword) {
                        echo '<div class="alert alert-danger">Passwords do not match.</div>';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
                        $stmt->bind_param("ss", $username, $hashedPassword);

                        if ($stmt->execute()) {
                            echo '<div class="alert alert-success">Admin user created successfully! You can now log in at <a href="login.php">Admin Login</a>.</div>';
                            echo '<p>After logging in, delete this setup.php file for security.</p>';
                        } else {
                            echo '<div class="alert alert-danger">Error creating admin user: ' . $conn->error . '</div>';
                        }
                        $stmt->close();
                    }
                }
                ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Admin</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>