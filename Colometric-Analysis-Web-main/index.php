<?php 
session_start();
?>
 
<?php require_once 'nav.php'; ?>     
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 d-flex flex-column justify-content-center"> 
                <h1 class="text-center">
                    <?php
                        if (isset($_SESSION['user_id']) && isset($_SESSION['name'])) {
                            echo "Hi, " . htmlspecialchars($_SESSION['name']) . "! Analyze Urine Test Strip";
                        } else {
                            echo "Welcome! Analyze Urine Test Strip";
                        }
                    ?>
                </h1>
                <p class="text-center mt-3">URINALYZE is a web-based application designed to streamline the analysis of urine test strips. It allows users to upload images of test strips, process results, and track past analyses through a dedicated history page.</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn mt-3" style="background-color: #262633; border: 2px solid white; border-radius: 20px; color: white;" onclick="window.location.href='upload.php'">Get Started</button>
                <?php else: ?>
                    <button class="btn mt-3" style="background-color: #262633; border: 2px solid white; border-radius: 20px; color: white;" onclick="window.location.href='login.php'">Get Started</button>
                <?php endif; ?>
            </div>
            <div class="col-md-6 mt-5 d-flex justify-content-center"> 
                <img src="assets/LogoMain.svg" class="img-fluid" alt="Logo" style="max-width: 80%;">
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
