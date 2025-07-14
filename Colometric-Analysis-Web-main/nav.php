<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'DATABASE/function.php'; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['email']) && isset($input['name'])) {
        $email = htmlspecialchars($input['email']);
        $name = htmlspecialchars($input['name']);
        
        $userId = $_SESSION['user_id']; // Get the logged-in user's ID from the session
        $updateData = [
            'email' => $email,
            'name' => $name
        ];

        if (!empty($input['password'])) {
            $updateData['password'] = htmlspecialchars($input['password']);
        }

        $updateResult = $db->update('users', $updateData, [
            'id' => $userId
        ]);

        if ($updateResult === true) {
            $_SESSION['useremail'] = $email;
            $_SESSION['name'] = $name;

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $updateResult]);
        }
    }  
}
$current_page = basename($_SERVER['PHP_SELF']);

// Set dynamic page title
$page_titles = [
    'index.php'    => 'URINALYZE - Home',
    'login.php'    => 'URINALYZE - Login',
    'logout.php'   => 'URINALYZE - Logout',
    'upload.php'   => 'URINALYZE - Strip Analysis',
    'history.php'  => 'URINALYZE - History'
];
$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'URINALYZE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #262633;
            color: white;
        }
        .navbar {
            background-color: #23232E;
        }
        .navbar-brand {
            color: white;
        }
        .nav-link {
            color: white;
        }
        .nav-link.active, .nav-link:focus, .nav-link:hover {
            color: #F1BB65 !important;
            background-color: #23232E !important;
            font-weight: bold;
            border-radius: 4px;
        }
        /* Remove hover/active/focus highlight for Profile */
        #profileButton.nav-link.active,
        #profileButton.nav-link:focus,
        #profileButton.nav-link:hover {
            color: white !important;
            background-color: transparent !important;
            font-weight: normal;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255, 255, 255, 1%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <a style="color: #F1BB65" class="navbar-brand" href="index.php">URINALYZE</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link<?php if ($current_page == 'index.php') echo ' active'; ?>" href="index.php">Home</a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link<?php if ($current_page == 'upload.php') echo ' active'; ?>" href="upload.php">Strip Analysis</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if ($current_page == 'history.php') echo ' active'; ?>" href="history.php">History</a>
                </li>
            <?php endif; ?>
            <?php if (isset($_SESSION['useremail'])): ?>
                <li class="nav-item">
                    <a class="nav-link<?php if ($current_page == 'profile.php') echo ' active'; ?>" href="#" id="profileButton">Profile</a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a class="nav-link<?php if ($current_page == 'logout.php') echo ' active'; ?>" href="logout.php" onclick="logout()">Logout</a>
                <?php else: ?>
                    <a class="nav-link<?php if ($current_page == 'login.php') echo ' active'; ?>" href="login.php">Login</a>
                <?php endif; ?>
            </li>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Only add event listener if profileButton exists
    var profileBtn = document.getElementById('profileButton');
    if (profileBtn) {
        profileBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent navigation
            const email = "<?php echo $_SESSION['useremail']; ?>";
            const name = "<?php echo $_SESSION['name']; ?>";
            Swal.fire({
                title: 'Update Profile',
                html: `
                <input type="text" id="name" class="swal2-input" placeholder="Name" value="${name}">
                <input type="email" id="email" class="swal2-input" placeholder="Email" value="${email}">
                <input type="password" id="password" class="swal2-input" placeholder="Password">
                `,
                confirmButtonText: 'Save',
                focusConfirm: false,
                preConfirm: () => {
                    const email = document.getElementById('email').value;
                    const name = document.getElementById('name').value;
                    const password = document.getElementById('password').value;
                    if (!email) {
                        Swal.showValidationMessage('Please enter an email');
                    }
                    return { email: email, password: password, name: name };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send the data to the server via AJAX
                    fetch('nav.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(result.value)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Saved!', '', 'success');
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Email already exist.', 'error');
                    });
                }
            });
        });
    }
</script>
        </ul>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['email']) && isset($input['password'])) {
                $email = htmlspecialchars($input['email']);
                $password = htmlspecialchars($input['password']);
                echo json_encode(['success' => true]);
            }  
        }
        ?>
    </div>
</nav>
<script>
function logout() {
    fetch('logout.php', { method: 'POST' })
        .then(() => {
            window.location.href = 'login.php';
        });
}
</script>
<?php 
// Route: /nav.php
// Handles: AJAX POST for profile update, and logout logic
?>