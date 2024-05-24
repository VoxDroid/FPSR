<?php
$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page === 'index.php') {
    // Include scripts specific to index.php
    echo <<<HTML
    <!-- Bootstrap JS -->
    <script src="JS/jquery.slim.min.js"></script>
    <script src="JS/popper.min.js"></script>
    <script src="JS/bootstrap.min.js"></script>
    <script src="JS/FA-all.js"></script>

    <!-- Custom JS -->
    <script src="JS/custom_script.js"></script>
HTML;
} else {
    // Include scripts for other pages (assuming they are in a subdirectory)
    echo <<<HTML
    <!-- UPPER Bootstrap JS -->
    <script src="../JS/jquery.slim.min.js"></script>
    <script src="../JS/popper.min.js"></script>
    <script src="../JS/bootstrap.min.js"></script>
    <script src="../JS/FA-all.js"></script>

    <!-- UPPER Custom JS -->
    <script src="../JS/custom_script.js"></script>
HTML;
}
?>
