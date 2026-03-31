<?php
echo "halo"

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="layout">

        <!-- Sidebar -->
        <?php include "includes/sidebarai.php"; ?>

        <div class="content">

            <!-- MAIN CONTENT -->
            <main class="main" id="main">
                <div class="content-wrap">
                    <!-- Area kanan -->
                    <div class="right">
                        <?php include "includes/header.php"; ?>

                        <h2>Dashboard</h2>
                    </div>
                </div>
            </main>
        </div>
    </div>
        <script src="assets/app.js"></script>
</body>

</html>