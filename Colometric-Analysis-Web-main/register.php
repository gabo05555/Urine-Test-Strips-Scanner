<?php 
// Route: /register.php
// Handles: POST for user registration

session_start();
require_once 'DATABASE/function.php'; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Capture confirm password

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        $user = $db->select('users', '*', ['email' => $email]);

        if ($user) {
            echo "<script>alert('Email already exists.');</script>";
        } else {
            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $password, // Store plain text password
                'type' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $db->insert('users', $data);

            if (isset($result['status']) && $result['status'] == 'error') {
                echo "<script>alert('{$result['message']}');</script>";
            } else {
                header('Location: login.php');
                exit();
            }
        }
    }
}
?>
<?php require_once 'nav.php'; ?>    
<style>
body, html {
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
}
.register-bg {
    min-height: 100vh;
    width: 100vw;
    background: #262633;
    display: flex;
    align-items: center;
    justify-content: center;
}
@media (max-width: 576px) {
    .register-bg {
        align-items: flex-start;
        padding-top: 32px;
        padding-bottom: 32px;
    }
    .register-card {
        margin: 16px;
    }
}
.register-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 40px 32px 32px 32px;
    width: 100%;
    max-width: 450px;
}
.register-card .form-control {
    background: #f8f9fa;
    border: 1px solid #e3e6ea;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 1.1rem;
}
.register-card .btn-primary {
    background: #262633;
    border: 2px solid #fff;
    border-radius: 999px;
    color: #fff;
    font-size: 1.1rem;
    padding: 10px 0;
    transition: none;
}
.register-card .btn-primary:focus, .register-card .btn-primary:hover {
    background: #262633;
    color: #fff;
    border: 2px solid #fff;
}
.register-card .form-group label {
    color: #262633;
    font-weight: 500;
}
.register-card a {
    color: #2186eb;
}
</style>
<div class="register-bg">
    <div class="register-card">
        <h1 class="text-center mb-4" style="font-weight: 600; color: #262633;">Register</h1>
        <form action="register.php" method="post"> 
            <div class="form-group"> 
                <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
            </div>
            <div class="form-group"> 
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required>
            </div>
            <div class="form-group"> 
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group"> 
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php">Already have an account? Login here</a>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>