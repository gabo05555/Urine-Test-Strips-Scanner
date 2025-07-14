<?php 
session_start();
require_once 'DATABASE/function.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = [];
    }

    if (isset($_SESSION['failed_attempts'][$email]) && $_SESSION['failed_attempts'][$email]['count'] >= 3) {
        $lockout_time = $_SESSION['failed_attempts'][$email]['time'];
        if (time() - $lockout_time < 300) { 
            echo "<script>alert('Too many failed attempts. Please try again after 5 minutes.'); window.location.href = 'login.php';</script>";
            exit();
        } else {
            unset($_SESSION['failed_attempts'][$email]);
        }
    }

    $user = $db->select('users', '*', ['email' => $email]);

    if ($user) {
        if ($password == $user[0]['password']) {
            $_SESSION['useremail'] = $user[0]['email']; // Store all user information in the session
            $_SESSION['user_id'] = $user[0]['id'];
            $_SESSION['name'] = $user[0]['name'];
            if ($user[0]['type'] == 0) {
                header('Location: index.php');
            } else {
                header('Location: table.php?table=history');
            }
            echo'shit';
            exit();

        } else {
 
            if (!isset($_SESSION['failed_attempts'][$email])) {
                $_SESSION['failed_attempts'][$email] = ['count' => 1, 'time' => time()];
            } else {
                $_SESSION['failed_attempts'][$email]['count']++;
                $_SESSION['failed_attempts'][$email]['time'] = time();
            }
            echo "<script>alert('Invalid email or password.'); window.location.href = 'login.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password.'); window.location.href = 'login.php';</script>";
    }
}
?>

<?php require_once 'nav.php'; ?>    
<style>
    .form-control:focus, .form-select:focus {

box-shadow: none !important;

}
body, html {
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
}
.login-bg {
    min-height: 100vh;
    width: 100vw;
    background: #262633;
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 40px 32px 32px 32px;
    width: 100%;
    max-width: 450px;
    margin: 16px;
}
@media (min-width: 576px) {
    .login-card {
        margin: 0;
    }
}
.login-card .form-control {
    background: #f8f9fa;
    border: 1px solid #e3e6ea;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 1.1rem;
}
.login-card .btn-primary {
    background: #262633;
    border: 2px solid #fff;
    border-radius: 999px;
    color: #fff;
    font-size: 1.1rem;
    padding: 10px 0;
    transition: none;
}
.login-card .btn-primary:focus, .login-card .btn-primary:hover {
    background: #262633;
    color: #fff;
    border: 2px solid #fff;
}
</style>
<div class="login-bg">
    <div class="login-card">
        <h1 class="text-center mb-4" style="font-weight: 600; color: #262633;">Login</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="post"> 
            <div class="form-group"> 
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required>
            </div>
            <div class="form-group"> 
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="register.php">Don't have an account? Register here</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>