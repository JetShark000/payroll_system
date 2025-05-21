<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login to Payroll System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f4f4; }
        .login-container {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 18px #ccc;
            padding: 32px 28px 24px 28px;
        }
        .login-title {
            text-align: center;
            margin-bottom: 24px;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-title">Payroll System Login</div>
    <form method="post">
        <div class="mb-3">
            <label for="user_id" class="form-label">User ID</label>
            <input type="number" class="form-control" name="user_id" id="user_id" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
    <?php
    if (isset($_POST['login'])) {
        $user_id = $_POST['user_id'];
        $password = $_POST['password'];

        if (empty($user_id) || empty($password)) {
            echo "<div class='alert alert-danger mt-3 text-center'>You need to fill in all the fields</div>";
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = $user;
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_panel.php");
                } else {
                    header("Location: employee_panel.php");
                }
                exit();
            } else {
                echo "<div class='alert alert-danger mt-3 text-center'>Incorrect password. Please try again.</div>";
            }
        } else {
            echo "<div class='alert alert-danger mt-3 text-center'>User ID not found. Please try again.</div>";
        }
    }
    ?>
</div>
</body>
</html>
