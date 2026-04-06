<?php
require_once 'includes/auth_check.php';
?>

<!DOCTYPE html>
<html lang="id">


<?php 
$title = "Dashboard";

include "includes/head.php"; ?>

<body>
    
    <div class="layout">

        <!-- Sidebar -->
        <?php include "includes/sidebarai.php"; ?>

            <main class="main" id="main">

                <?php include "includes/header.php"; ?>
                <div class="content-wrap">
                        <h2>Dashboard</h2>
                    </div>
            </main>
    </div>
        <script src="assets/app.js"></script>
</body>

</html>